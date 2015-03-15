<?php
/**
 * file-system 
 * @version: 1.0.0
 *
 * @file: File.php
 * @author Ashterix <ashterix69@gmail.com>
 *  
 * Class - File
 * @description 
 *
 * Created by JetBrains PhpStorm.
 * Date: 14.03.2015
 * Time: 13:58
 */

namespace UFOFilesystem;


class File extends FilesystemBase {

    protected $newContent = '';
    protected $newContentReplace = false;

    const MODE_REPLACE_CONTENT = "w+";
    const MODE_ADD_CONTENT = "a+";

    /**
     * Create object.
     * Construct parent
     *
     * @param $path
     */
    public function __construct($path)
    {
        /** @noinspection PhpParamsInspection */
        parent::__construct($path, self::TYPE_FILE);
    }

    /**
     * @description Apply settings
     */
    public function save() {
        if ($this->isset == false){
            // if not isset, create new file
            $folder = new Folder(dirname($this->path));
            $folder->save();

            $mode = self::MODE_ADD_CONTENT;
            if ($this->newContentReplace) {
                $mode = self::MODE_REPLACE_CONTENT;
            }
            $this->write($mode);
        }
        // and set configs to file
        $this->update();
    }

    /**
     * @description write to file
     *
     * @param $mode
     */
    private function write($mode)
    {
        $file = fopen($this->path, $mode);
        fwrite($file, $this->newContent . PHP_EOL);
        fclose($file);
        $chmod = (!empty($this->configChmod)) ? $this->configChmod : self::CHMOD;
        chmod($this->path, $chmod);

    }

    /**
     * @description Update file
     */
    private function update()
    {
        if (!empty($this->configChmod)) {
            chmod($this->path, $this->configChmod);
        }

        if (!empty($this->configRename)) {
            // TODO: ash-1: implement rename if file already exist
            rename($this->path, $this->configRename);
        }

    }

    /**
     * @description Remove folder if empty
     *
     * @throws \Exception
     */
    public function remove($path = null) {
        if (empty($path)) {
            $path = $this->path;
        }

        if ($this->isset) {
            unlink($path);
        }
    }

    /**
     * @description Set content to file
     *
     * @param $newContent
     * @param bool $replace Flag indicating whether you want to replace or append content
     * @return $this
     */
    public function setNewContent($newContent, $replace = false)
    {
        $this->newContent = $newContent;
        $this->newContentReplace = $replace;
        return $this;
    }
}