<?php
use UFOFilesystem\Folder;

require_once '../vendor/autoload.php';


// Create
$myFolder = new Folder("MyFolder");
$myFolder->setConfigChmod(0777)
    ->save();

// Rename
$myFolder->setConfigRename("MyFolder2")->save();

// Get folder content
foreach($myFolder->ls() as $file) {
    // do something
}

// Delete
$myFolder->remove();



