<?php
/**
 * file-system 
 * @version: 1.0.0
 *
 * @file: Folder.php
 * @author Ashterix <ashterix69@gmail.com>
 *  
 * Class - Folder
 * @description 
 *
 * Created by JetBrains PhpStorm.
 * Date: 14.03.2015
 * Time: 13:52
 */

namespace UFOFileSystem;

class Folder extends FileSystemBase {

    /**
     * Settings
     */
    protected $configAccessDeny = false;

    /**
     * Create object.
     * Construct parent
     *
     * @param $path
     */
    public function __construct($path)
    {
        /** @noinspection PhpParamsInspection */
        parent::__construct($path, self::TYPE_FOLDER);
    }

    /**
     * @description Get all child folders and files
     *
     * @return array
     */
    public function ls()
    {
        return ($this->isset) ? glob($this->path . "/*") : [];
    }

    /**
     * @description Apply settings
     */
    public function save() {
        if ($this->isset == false){
            // if not isset, create new folder
            $pathArray = explode('/', $this->path);
            $pathForCreate = [];
            $tmp = [];
            foreach ($pathArray as $i => $pathPiece) {
                $tmp[$i] = $pathPiece;
                $pathForCreate[$i] = implode("/", $tmp);
                $this->create($pathForCreate[$i]);
            }
        }
        // and set configs to folder
        $this->update();
    }

    /**
     * @description Create folder
     *
     * @param $folderName
     */
    private function create($folderName)
    {
        if (!empty($folderName) && !file_exists($folderName)){
            $chmod = (!empty($this->configChmod)) ? $this->configChmod : self::CHMOD;
            mkdir($folderName, $chmod);
        }
    }

    /**
     * @description Update folder
     */
    private function update()
    {
        if (!empty($this->configChmod)) {
            chmod($this->path, $this->configChmod);
        }

        if ($this->configAccessDeny) {
            $indexFile = new File($this->path . "/index.html");
            $indexFile->setNewContent("Access denied")
                ->save();
        }

        if (!empty($this->configRename)) {
            // TODO: ash-1: implement rename if folder already exist
            rename($this->path, $this->configRename);
        }

    }

    /**
     * @description Remove folder if empty
     *
     * @throws \Exception
     */
    public function remove()
    {
        $this->removeBase();
    }

    /**
     * @description Remove folder and all child folders and files
     */
    public function removeForce()
    {
        $this->removeBase(true);
    }

    /**
     * @description Base method for remove
     *
     * @param bool $force
     * @param null | string $path
     * @throws \Exception
     */
    private function removeBase($force = false, $path = null) {
        if (empty($path)) {
            $path = $this->path;
        }

        if (is_file($path)) {
            $file = new File($path);
            $file->remove();
        } else {
            $folderContents = glob($path . "/*");

            if ($force == false && count($folderContents) > 0) {
                throw new \Exception("Failed to remove. Folder is not empty. Use the method \"Folder::removeForce()\"");
            }

            foreach ($folderContents as $folderContent) {
                $this->removeBase($force, $folderContent);
            }
            if ($this->isset) {
                rmdir($path);
            }
        }

    }

    // Config installers

    /**
     * @description Close access to the folder via http
     *
     * @return $this
     */
    public function setConfigAccessDeny()
    {
        $this->configAccessDeny = true;
        return $this;
    }

}