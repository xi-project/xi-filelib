# Filelib

File library for PHP 5.3 applications. See the wiki for documentation.

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

# wiring

    $filelib = new FileLibrary(
        new FilesystemStorage(),
        new SymlinkPublisher(),
        new Backend(
            new MongoPlatform(),
        )
    );

    $file = $filelib->uploadFile(
