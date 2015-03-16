<?php
use UFOFilesystem\File;

require_once '../vendor/autoload.php';

$myFile = new File("test.txt");
$myFile->->setConfigChmod(0655)->
