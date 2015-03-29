<?php
/**
 * file-system 
 * @version: 1.0.0
 *
 * @file: FileSystemBase.php
 * @author Ashterix <ashterix69@gmail.com>
 *  
 * Class - FileSystemBase
 * @description 
 *
 * Created by JetBrains PhpStorm.
 * Date: 15.03.2015
 * Time: 21:59
 */

namespace UFOFileSystem;


use UFOFileSystem\Interfaces\FileSystemBaseInterface;

class FileSystemBase implements FileSystemBaseInterface {

    protected $path = '';
    protected $isset = false;

    /**
     * Settings
     */
    protected $configChmod = 0;
    protected $configRename = '';

    /**
     * Default settings
     */
    const CHMOD = 0755;

    /**
     * Create object.
     * Set folder or file path
     * Check folder or file on the path
     *
     * @param $path
     * @param $type file or folder
     * @throws \Exception
     */
    public function __construct($path, $type)
    {
        if (empty($path)) {
            throw new \Exception("The path can not be empty");
        }

        $this->path = $path;
        $this->checkAvailability($type);
    }

    /**
     * @description Check folder or file on the path
     */
    protected function checkAvailability($type)
    {
        if ($type == self::TYPE_FOLDER) {
            if (is_dir($this->path)){
                $this->isset = true;
            }
        } elseif ($type == self::TYPE_FILE) {
            if (is_file($this->path)){
                $this->isset = true;
            }
        }
    }


    // Config installers

    /**
     * @description Chmod for folder
     *
     * @param $configChmod
     * @return $this
     */
    public function setConfigChmod($configChmod)
    {
        $this->configChmod = $configChmod;
        return $this;
    }

    /**
     * @description Set new name for folder or file
     *
     * @param $configRename
     * @return $this
     */
    public function setConfigRename($configRename)
    {
        $this->configRename = $configRename;
        return $this;
    }


}