UFO file system
=========
A set of classes for working with files.


Requirement
-----------

- PHP >= 5.4

Installation
------------

Via [Composer][]:

    require "ufo-cms/file-system": "dev-master"


Usage
-----

```php
use UFOFilesystem\Folder;
use UFOFilesystem\File;
```


### Folder
##### Create folder
```php
// create a new folder in the current folder
$myFolder = new Folder("MyFolder");
$myFolder->setConfigChmod(0777)->save();

// or create a new folder and subfolder
$mySubFolder = new Folder("MyFolder/MySubFolder");
$mySubFolder->setConfigChmod(0777)->save();
```

##### Rename folder
```php
// rename a existing folder
$myFolder = new Folder("MyFolder");
$myFolder->setConfigRename("NewNameMyFolder")->save();
```

##### Remove folder
```php
// Delete a existing folder
$myFolder = new Folder("MyFolder");
$myFolder->remove();
```

##### Get folder content
```php
$myFolder = new Folder("MyFolder");
foreach($myFolder->ls() as $file) {
    // do something
}
```


### File
##### Add content to file
Creates the file if it does not exist.
```php
$contentForSaveToFile = "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";

$myFile = new File("Lorem.txt");
$myFile->setContent($contentForSaveToFile)->save();
```

##### Rename a file
```php
$myFile->setConfigRename("Lorem2.txt")->save();
```

##### Remove a file
```php
$myFile->remove();
```

License
-------

This library is available under the GPL-2.0+ license.

[Composer]: http://getcomposer.org/







