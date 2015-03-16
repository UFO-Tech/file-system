<?php
use UFOFilesystem\File;

require_once '../vendor/autoload.php';

$contentForSaveToFile = "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";


// Create
$myFile = new File("Lorem.txt");
$myFile->setConfigChmod(0655)
    ->setContent($contentForSaveToFile)
    ->save();

// Rename
$myFile->setConfigRename("Lorem2.txt")->save();

// Delete
$myFile->remove();
