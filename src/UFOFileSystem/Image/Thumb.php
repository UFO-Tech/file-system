<?php
/**
 * UFO-CMS
 * @version: 1.0.0
 *
 * @file: Thumb.php
 * @author Ashterix <ashterix69@gmail.com>
 *
 * Class - Thumb
 * @description
 *
 * Created by JetBrains PhpStorm.
 * Date: 15.02.15
 * Time: 16:59
 */
// TODO ash-1 рефакторить этот файл

namespace UFO\Core\Image\FileSystem;


class Thumb
{
    private $actual_width;      // актуальная ширина
    private $actual_height;     // актуальная высота
    private $filename;          // имя файла
    private $cut;               // статус обрезки по рамке
    private $cut_resize;        // статус изменения по рамке
    private $rotate;            // актуальное вращение
    private $water_mark=false;  // наложение водяного знака (по-умолчанию нет)
    private $water_mark_text=NULL;  // текст водяного знака (по-умолчанию аддрес сайта)

    private $mime_type_settings = array();
    private $save_status;               // сохранение в файл (по-умолчанию true)
    private $new_name_folder    = '/thumb'; //имя папки thumb
    private $new_name_format    = '%s.%sx%s.r_%s.%s.wm_%s'; //формат имени thumb
    private $default_width      = 640;  // ширина по-умолчанию
    private $default_height     = 480;  // высота по-умолчанию
    private $jpeg_quality       = 90;   // качество файлов jpeg
    private $default_rotate     = 0;    // поворот по-умолчанию

    public function __construct($save_in_file=true) {
        ini_set("memory_limit", "128M");
        $this->actual_width = (isset($_GET['w']))?$_GET['w']:false;
        $this->actual_height = (isset($_GET['h']))?$_GET['h']:false;
        $this->water_mark = (isset($_GET['wm']))?true:false;
        $this->water_mark_text = (!empty($_GET['wm']))?$_GET['wm']:_UFO_DOMAIN_NAME_;
        if (!$this->actual_width && !$this->actual_height) {
            // вписать в рамку по умолчанию
            $this->actual_width = $this->default_width;
            $this->actual_height = $this->default_height;
        }

        $this->filename = $_GET['name'];

        // проверяем наличие папки thumb и создаём, если её нет
        add_folder(dirname($this->filename) . $this->new_name_folder."/");

        $this->cut = isset($_GET['c']);
        $this->cut_resize = isset($_GET['tc']);

        $this->rotate = (isset($_GET['r']))?$_GET['r']:$this->default_rotate;

        $this->set_mime_types();

        $this->save_status = $save_in_file;
        $this->init();
    }


    private function init() {
        $info = getimagesize($this->filename);

        if (!file_exists($this->filename) || !is_file($this->filename)) exit;
        if (!$info || !isset($this->mime_type_settings[$info['mime']])) exit;

        $settings = $this->mime_type_settings[$info['mime']];
        $settings_backup = $settings;

        $cur_width  = $info[0];
        $cur_height = $info[1];
        $dst_x = $dst_y = $scr_x = $scr_y = 0;

        if ($this->rotate!=0){
            $this->rotate = -$this->rotate;
            $this->rotate = intval(-$this->rotate) % 4;
            if ($this->rotate % 2 && $cur_width != $cur_height) {
                list($cur_width, $cur_height) = array($cur_height, $cur_width);
                list($this->actual_width, $this->actual_height) = array($this->actual_height, $this->actual_width);
            }
        }

        if (!$this->actual_width && $this->actual_height) {
            // вписываем по высоте
            $new_width  = $this->actual_width = floor($cur_width * $this->actual_height / $cur_height);
            $new_height = $this->actual_height;
        } elseif ($this->actual_width && !$this->actual_height) {
            // вписываем по ширине
            $new_width  = $this->actual_width;
            $new_height = $this->actual_height = floor($cur_height * $this->actual_width / $cur_width);
        } elseif ($this->cut) {
            // вписываем с обрезкой
            $scale_w = $this->actual_width / $cur_width;
            $scale_h = $this->actual_height / $cur_height;
            $scale = max($scale_w, $scale_h);
            $new_width  = floor($cur_width * $scale);
            $new_height = floor($cur_height * $scale);
            $dst_x = floor(($this->actual_width - $new_width) / 2);
            $dst_y = floor(($this->actual_height - $new_height) / 2);
        } elseif ($this->cut_resize) {
            // принудительно изменяем размеры
            $scale_w = $this->actual_width / $cur_width;
            $scale_h = $this->actual_height / $cur_height;
            $scale = max($scale_w, $scale_h);
            if ($scale == $scale_h){
                $new_width  = $this->actual_width;
                $new_height = floor($cur_height * $this->actual_width / $cur_width);
                // высчитываем разницу в высоте
                $dst_y = floor(($this->actual_height - $new_height))/2;
            } else {
                $new_width  = floor($cur_width * $this->actual_height / $cur_height);
                $new_height = $this->actual_height;
                // высчитываем разницу в ширине
                $dst_x = floor(($this->actual_width - $new_width))/2;
            }
            // переопределяем тип на png
//            $info['mime'] = 'image/png';
//            $settings = $this->mime_type_settings[$info['mime']];
//            $settings['create'] = $settings_backup['create'];
        } else {
            // вписываем без обрезки
            $scale_w = $this->actual_width / $cur_width;
            $scale_h = $this->actual_height / $cur_height;
            $scale = min($scale_w, $scale_h);
            $new_width  = $this->actual_width = floor($cur_width * $scale);
            $new_height = $this->actual_height = floor($cur_height * $scale);
        }

        if ($this->rotate &&($this->actual_width > $cur_width || $this->actual_height > $cur_height)) {
            header('Content-type: ' . $info['mime']);
            readfile($this->filename);
            exit;
        }

        $wm_for_name = 0;
        if ($this->water_mark){
            $wm_for_name = Text::translit($this->water_mark_text,'_');
        }
        $cut = 0;
        if($this->cut){
            $cut = 'cut';
        } elseif($this->cut_resize){
            $cut = 'resize';
        }
        $thumb_filename = dirname($this->filename) . $this->new_name_folder. '/'
            . sprintf($this->new_name_format, basename($this->filename, $settings_backup['ext']), $this->actual_width, $this->actual_height, $this->rotate, $cut, $wm_for_name)
            . $settings['ext']
        ;

        if (file_exists($thumb_filename) && filemtime($thumb_filename) >= filemtime($this->filename)) {
            header('Content-type: ' . $info['mime']);
            readfile($thumb_filename);
            exit;
        }
        $cur_img = call_user_func($settings['create'], $this->filename);
        if ($this->rotate!=0){
            ini_set("memory_limit", "-1");
            $cur_img = imagerotate($cur_img, $this->rotate * 90,0);
        }
        $tmp_img  = imagecreatetruecolor($this->actual_width, $this->actual_height);

        // определяем цвет и прозрачность первого пикселя исходной картинки
        $rgba = imagecolorat($cur_img, 0, 0);
        $r = ($rgba >> 16) &0xFF;
        $g = ($rgba >> 8) &0xFF;
        $b = $rgba &0xFF;
        $a = ($rgba &0x7F000000) >> 24;
        $bg_color = imagecolorallocatealpha($tmp_img, $r, $g, $b, $a);
        // заливаем новую картинку этим цветом
        imagefill($tmp_img, 0, 0, $bg_color);

        if ($settings['ext']=='.png'){
            imagealphablending($tmp_img, false);
            imagesavealpha($tmp_img, true);
        }

        // копируем картинку с изменением размера
        imagecopyresampled(
            $tmp_img, $cur_img,
            $dst_x, $dst_y,
            $scr_x, $scr_y,
            $new_width, $new_height,
            $cur_width, $cur_height
        );
        if ($this->water_mark){
            $fontpath = _UFO_PATH_ASSETS_FONTS_;
            putenv('GDFONTPATH='.$fontpath);
            $width = imagesx($tmp_img);
            $height = imagesy($tmp_img);
            $angle =  -rad2deg(atan2((-$height),($width)));
            $text = " ".$this->water_mark_text." ";//текст надписи
            $font = $fontpath."/arial.ttf";

            // не забываем про сопряжение прозрачного фона водяного знака
            imagealphablending($tmp_img, true);
            $c = imagecolorallocatealpha($tmp_img, 0, 0, 2, 115);
            $size = (($width+$height)/2)*2/strlen($text);
            $box  = imagettfbbox ( $size, $angle, $font, $text );
            $x = $width/2 - abs($box[4] - $box[0])/2;
            $y = $height/2 + abs($box[5] - $box[1])/2;

            imagettftext($tmp_img, $size ,$angle, $x, $y, $c, $font, $text);
        }

        imagedestroy($cur_img);
        header('Content-type: image/png' );
        call_user_func($settings['save'], $tmp_img, $thumb_filename);
        imagedestroy($tmp_img);
        exit;
    }

    private function save_gif($img, $filename = false) {
        if ($filename !== false && $this->save_status) {
            imagegif($img, $filename);
        }
        imagegif($img);
    }

    private function save_jpg($img, $filename = false) {
        if ($filename !== false && $this->save_status) {
            imagejpeg($img, $filename, $this->jpeg_quality );
        }
        imagejpeg($img, '', $this->jpeg_quality );
    }

    private function save_png($img, $filename = false) {
        imagealphablending($img, false);
        imagesavealpha($img, true);
        if ($filename !== false && $this->save_status) {
            imagepng($img, $filename);
        }
        imagepng($img);
    }


    private function set_mime_types(){
        $this->mime_type_settings = array(
            'image/gif'  => array(
                'ext'       => '.gif',
                'create'    => 'imagecreatefromgif',
                'save'      => array(&$this, 'save_gif'),
            ),
            'image/jpeg'  => array(
                'ext'       => '.jpg',
                'create'    => 'imagecreatefromjpeg',
                'save'      => array(&$this, 'save_jpg'),
            ),
            'image/pjpeg'  => array(
                'ext'       => '.jpg',
                'create'    => 'imagecreatefromjpeg',
                'save'      => array(&$this, 'save_jpg'),
            ),
            'image/png'  => array(
                'ext'       => '.png',
                'create'    => 'imagecreatefrompng',
                'save'      => array(&$this, 'save_png'),
            ),
        );
    }

}
