# Xi Filelib

[![Build Status](https://secure.travis-ci.org/xi-project/xi-filelib.png?branch=master)](http://travis-ci.org/xi-project/xi-filelib)

Filelib is a file library component for PHP, providing a virtual filesystem for your web application's files.
It can be used to manage both your application's internal and user-uploaded files in many ways. What it (at least for now)
is NOT is a place to store your assets like css, js and similar.

Let's face it: practically all web apps have to store documents, media and such and the needs are same.
Filelib takes care of all the hard and/or repetetive tasks and abstracts away and reveals all the related changeable
components as loosely coupled subsystems:

* Storing of metadata (virtual folders / files) in a data storage (database, usually).
* Safe physical storage of files in a filesystem (local, S3, Gridfs, etc).
* Authorization (who can do what in the virtual filesystem) and publication (to make them fast-readable and with
  pretty urls) of world readable files.
* Rendering the files to HTTP response (sometimes you can't world-publish everything but it's still gotta be decent).
* Mime types and extensions and all this horrible stuff
* Versioning (meaning creation of different representations of a master file, thumbnails, html5 videos etc)
* Asynchronous processing (you can't keep the end user waiting for that video to be encoded, you know)
* And there's probably more!

Filelib is fully extensible via plugins and hooks. In fact, many of the "core" functionality is provided
via plugins (authorization, automatic publishing, file versions) so the core is kept elegant and maintainable.

Filelib is based on my own observations, opinions and experience formed while developing many
file- and mediabanks for the last 10 years. It has evolved and keeps evolving with real projects and use cases,
so thanks for all past and present early adopters!

## Hard requirements

- A client software that needs file management
- PHP 5.4.x
- Json support for a poor poor man's data storage

## Soft requirements (for harder use)

- A serious data storage (MySQL/MariaDB, PostgreSQL, MongoDB supported out of the box)
- Imagemagick for image processing
- Zencoder for all your video needs
- Intl for transliterating / slugifying
- A queue (RabbitMQ, IronMQ, SQS) for asynchronous operations

## Quickstart

### Using JSON storage (for simple testing only)

```php
<?php

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Backend\Adapter\JsonBackendAdapter;
use Xi\Filelib\Storage\Adapter\FilesystemStorageAdapter;
use Pekkis\DirectoryCalculator\DirectoryCalculator;
use Pekkis\DirectoryCalculator\Strategy\UniversalLeveledStrategy;

$filelib = new FileLibrary(
    new FilesystemStorageAdapter(
        __DIR__ . '/files',
        new DirectoryCalculator(new UniversalLeveledStrategy()
    ),
    new JsonBackendAdapter(__DIR__ . '/filelib-example.json')
);

$file = $filelib->uploadFile('/path/to/some/file.jpg');

// Display the image
header("Content-Type: " . $file->getMimetype());
echo file_get_contents($filelib->getStorage()->retrieve($file->getResource()));

```

### Documentation

TODO!

### Examples and use cases

https://github.com/xi-project/xi-filelib-examples contains a lot of examples from very simple to very complex
use cases. Clone the repo, configure a web server and dive straight into the code!

## About integrating to your own software

Experience has time and again proved that integration should be light. If one is using Doctrine ORM, it
*could* seem appropriate to integrate via Filelib's entities. DON'T DO IT. It will bite you back.

All the stuff inside the backend platforms (and other deeper abstractions provided by Filelib) are the library's private
parts and subject to change any time. So if you utilize these internals, be prepared to enter upgrade hell at some
point. For example the entities in the ORM backend platform; they may go away for good some day.

Just utilize Filelib's identifiables' (folders / files) ids / UUIDs within your own data and domain and use functionality
provided by Filelib for everything else.

FolderRepository and FileRepository *should* usually be as deep as you have to go but it may not yet be the case. For
some "tougher" operations I've personally had to use Backend and ProfileManager, at least. These are things
and use cases that are yet to be considered before 1.0 is reached.

## Framework integration

For framework integration, see:

* https://github.com/xi-project/xi-bundle-filelib (Symfony bundle, needs a maintainer, I'm a very bad one!)
* https://github.com/xi-project/xi-filelib-symfony-sandbox (Symfony sandbox, needs a maintainer too!)

## Actual applications using Filelib

For actual applications I know are using Filelib, see:

* http://autot.oikotie.fi: Classified ads for cars. Ancient version if not upgraded without my knowledge / help.
* https://oy.eautokoulu.fi: Learning environment. Advanced large-scale usage of both images and videos. Current version.
* http://sykettatyohon.fi: Document / image managent provided by probably a somewhat older version.

Know more? Please tell!



