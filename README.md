# Filelib

Filelib is a file library component for PHP, providing a virtual filesystem for your web application's files.

Filelib's subcomponents (storing of file metadata, physical storage, access control, publishing of files) are loosely
coupled so you can mix and match to your heart's content and application's requirements.

Filelib is extensible via plugins. Core plugins include processing and versioning of both images and files.

## Hard requirements

- PHP 5.3.x

## Soft requirements (for any kind of serious use) vary

- A data storage (MySQL, PostgreSQL, MongoDB supported out of the box)
- Imagemagick
- Intl

## Quickstart

### Using JSON storage (for simple testing only)

```php
<?php

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Backend\Platform\JsonPlatform;
use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator;

$filelib = new FileLibrary(
    new FilesystemStorage(__DIR__ . '/files', new TimeDirectoryIdCalculator()),
    new JsonPlatform(__DIR__ . '/filelib-example.json')
);

$file = $filelib->upload('/path/to/some/file.jpg');

// Display the image
header("Content-Type: " . $file->getMimetype());
echo file_get_contents($filelib->getStorage()->retrieve($file->getResource()));

```

### Examples and use cases

̈́`docs/examples/index.php` contains a lot of  examples. Checkout the code, configure a web server and dive
straight into the code.

For framework integration, see:

* https://github.com/xi-project/xi-bundle-filelib

[![Build Status](https://secure.travis-ci.org/xi-project/xi-filelib.png?branch=master)](http://travis-ci.org/xi-project/xi-filelib)

