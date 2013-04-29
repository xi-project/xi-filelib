# Filelib

Filelib is a file library for PHP. See the wiki for more documentation.

## Requirements

- PHP 5.3.x
- A database for storing metadata like `MySQL`, `PostgreSQL`, `SQLite` or `MongoDB`

## Quickstart

### Using Doctrine ORM

Filelib must have a place to store both files and file metadata.

1. Use a database schema from `/docs/` to initialize your database tables.

For MySQL

    mysql -uroot -p filelib_example < schema-mysql.sql

2. Configure Filelib with directory paths for `private`, `public` and `temp`
   and make them writable by the web server.

```php
<?php

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\LeveledDirectoryIdCalculator;
use Xi\Filelib\Backend\Platform\DoctrineOrmPlatform;
use Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher;

/**
 * @var $entityManager Doctrine\ORM\EntityManager
 */

$filelib = new FileLibrary(
    new FilesystemStorage('/path/to/my/application/data/private', new LeveledDirectoryIdCalculator()),
    new DoctrineOrmPlatform($entityManager),
    new SymlinkFilesystemPublisher('/path/to/my/application/web/files', 0600, 0700, '/files')
);
$filelib->setTempDir('/path/to/my/application/data/temp');

$file = $filelib->upload('/path/to/some/file.jpg');
```

For integration, see:

* https://github.com/pekkis/xi-zend-filelib
* https://github.com/xi-project/xi-bundle-filelib

[![Build Status](https://secure.travis-ci.org/xi-project/xi-filelib.png?branch=master)](http://travis-ci.org/xi-project/xi-filelib)


# Usage

Explaining the mandatory subsystems:

Virtual filesystem with folders and files.

* ACL handles ACL
* Backend / Platform handles metadata storage
* Publisher publishes
* Storage stores physical files
