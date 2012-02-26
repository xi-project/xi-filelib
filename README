Filelib - file library for PHP 5.3
===================================

Filelib, **Filebanksta** among friends, is a reusable file library component for
PHP 5.3 applications.

After implementing too many file-, media-, and other bankstas for too many PHP
applications, a thought emerged. All the different filebankstas I had coded
did basically the same things.

1. A file is uploaded to the filebanksta.
2. You store it somewhere both physically (the concrete file itself) and
   metaphysically (the metadata)
3. The file must be versioned -- by versioning, I mean creating thumbnails
   for images, pdf's from documents and such, not the versioning of the
   history of a file.
4. Someone wants to access the file or one of it's versions. If you want to
   serve lots of files, you want to let the HTTP server do the serving (fast).
   But if you have to implement access control, PHP checks the permissions and
   then does the serving (slower).

Of course there's a lot of unforeseen depth inside every step in this process,
but the basic principles always remain the same. All the filebankstas do this
stuff and practically all PHP apps ever made need this kind of filebanksta
functionality.

I saw that a file library to end all the other file librarys was possible.
Thus, the Filebanksta was born.

Ideology
----------

The basic structure of Filebanksta is very simple. It has a virtual
filesystem consisting of _folders_ and _files_. One root folder can have
n+1 subfolders, and all folders can contain 0-n files.

Every file belongs to a _profile_. Profiles offer the possibility to
differentiate the roles and functions of files uploaded to the
system.

For example profile "default" could just store a file as-is,
and profile "versioned" could create context-specific versions for a file:
thumbnails for images, HTML 5 videos from videos, pdf:s from open office
documents, etc.

Filebanksta is extended via _plugins_. A plugin connects to 1-n profiles
and _provides_ for certain types of files (image versioning plugin only
reacts for images).

Plugins listen for _events_. Filebanksta implements the observer pattern
(with the Symfony 2 event dispatcher). Plugins just register with a set
of events and are notified when something interesting happens (a file is
uploaded, deleted, etc).

Subsystems
-----------

I separated the concerns of a file library application to easily
digestible subsystems, each implementing a common interface.

### Files and folders

Files and folders are always just "dum" value items.
Filebanksta defines an interface for both and supplies a default
implementation.

    <?php

    $folder = FolderItem::create(array(
        'id' => 3,
        'parent_id' => 2,
        'name' => 'subfolder',
        'url' => 'folder/subfolder',
    ));

    $file = FileItem::create(array(
        'id' => 1,
        'folder_id' => 3,
        'mimetype' => 'image/png',
        'profile' => 'versioned',
        'size' => 5000,
        'name' => 'doctor-vesala.png',
        'link' => 'folder/subfolder/doctor-vesala.png',
        'date_uploaded' => new DateTime('2012-01-01')
    ));

    ?>

### Backend

Backend is responsible for storing (and retrieving, and deleting, of course)
the metadata of the filebanksta. The virtual folders and files reside here.

At the moment filebanksta supplies implementations for SQL databases
via both Zend DB and Doctrine 2, and MongoDB via PECL's Mongo extension.

Of course the backend could be something radically different: XML inside a
physical directory structure inside a filesystem or whatnot. Just implement
the interface and you're good to go.

    <?php

    use Xi\Filelib\FileLibrary;
    use Xi\Filelib\Backend\Doctrine2Backend;

    $filelib = new FileLibrary();
    $backend = new Doctrine2Backend();
    $backend->setEntityManager($entityManager);

    $filelib->setBackend($backend);

    ?>


