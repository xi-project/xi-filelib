# Upgrading Filelib

Filelib is slowly but surely approaching acceptable stableness. About frakking time, one would say.
It's been developed for years. Too much incubation is too much.

## 0.13.1

* Added support for [Flysystem](https://github.com/thephpleague/flysystem)

## From version 0.12.x to version 0.13.x

* Some storages (Gaufrette, Filesystem) now expect an instance of PathCalculator so their signatures are
  changed a bit. Supply a LegacyPathCalculator and everything is well.

## From version 0.11.x to version 0.12.x

* Bumped up PHP requirement to 5.4.x
* Feature freeze. No more stuff before 1.0!
* Uh oh. Data is not backwards compatible. This is the last time before 1.0.
* All version identifiers are now classes of Xi\Filelib\Plugin\VersionProvider\Version.
* Version providers and all things related were heavily refactored.
* Publisher was heavily refactored. publish() is now publishAllVersions(). Functionality changed: read it through.
* Folders now have a data container object.
* Filelib's upload() shortcut is now uploadFile().
* Filelib has other convenient repository shortcuts (findFile(), findFiles() etc).
* FileRepository has some new nice finder methods (for example findMany()).
* "Find many" type of operations now return collections (xi/collections) instead of ArrayIterators,
  making nice functional style of programming possible out of the box.
* Refactored storage subcomponent.
    * Storages are now named StorageAdapters, wrapped by a common Storage class (basically the same interface as before)
    * Via storage events it is now possible to hook into the storing of files (to crypt/decrypt them, for example)
* FILE_BEFORE_CREATE event (FileUploadEvent) is now named FILE_UPLOAD.
* FILE_BEFORE_CREATE is now a _new_ event (FileEvent) triggered before creation of files.
* Filelib's addPlugin() has an optional $name parameter. A name will be generated for you if you do not provide one.
* Added a plugin manager
    * Has a getPlugin($name) method to fetch added plugins by name.
    * Has other more or less useful plugin related methods.
* AbstractVersionProvider doesn't have an identifier attribute any more.
* Minor other refactorations and simplifications in plugins. Check your custom ones, they may require small changes.
* Reversible versions of creation time and sequential linkers and a ReversibleLinkerInterface interface.
  It would be very wise to update to these (you'll have to unpublish and republish all your files).
* Plugins may now support lazy operations via extending LazyVersionProvider instead of VersionProvider.
  Lazy versions can be used to save space in large systems by deleting old / rarely used / whatever versions.
  They are then recreated when needed. When used in combination with the publisher, redirecting not-found
  published files to PHP backend must of course be done. (see docs/ for examples).
* There may be even more. Lost track at some point.

### PostgreSQL migration

```sql
ALTER TABLE xi_filelib_folder ADD COLUMN data TEXT NOT NULL DEFAULT '{}';
```

### MySQL migration

```sql
ALTER TABLE xi_filelib_folder ADD COLUMN data TEXT NOT NULL DEFAULT '{}';
```

### MongoDB migration

```
db.folders.update( { }, { $set: { data: {} } }, { multi: true });
```

## From version 0.10.x to version 0.11.x

* Data is backwards compatible. Hoorah!
* Only AfterUploadFileCommand is async-capable.
* Caching is available. Consider adding a memcache cache to your app via `Filelib::createCacheFromAdapter()`
  to get moar speed.
* All things operator related are renamed to repository. getFileOperator() -> getFileRepository() in your code.
* Profiles moved to profile manager
* Resource stuff moved from FileRepository to ResourceRepository
* All identifiable (resource, file, folder) constructors are now private. Use create() function to instantiate.
* RandomizeNamePlugin now uses Rhumsaa::uuid4() and generates an UUID based random name
* Renamed Platform (under Backend) to BackendAdapter for consistency's sake (everything else already uses this)
* Arbitrary data container API of resources and files has been refactored.
* ZendSlugifier is gone. If you use beautifurls, use the following replacement slugifier to get
  absolute 100% backwards compatibility.:

```php
use Xi\Filelib\Tool\Slugifier\Slugifier;
use Xi\Filelib\Tool\Slugifier\Adapter\PreTransliterator;
use Xi\Filelib\Tool\Slugifier\Adapter\CocurSlugifierAdapter;
use Xi\Transliterator\IntlTransliterator;

$slugifier = new Slugifier(new PreTransliterator(new IntlTransliterator(), new CocurSlugifierAdapter()));

// This may just be enough, at least if you republish all files.
$slugifier = new Slugifier();

```

## From version 0.9.x to version 0.10.x

* Data is backwards compatible. Hoorah!
* Zend Framework's AWS was not fun no more. Replaced with Amazon's own client. Update your composer.json if needed.
* Queue subsystem was extracted as [pekkis/queue](https://github.com/pekkis/queue) and refactored. So if you're
  hooked to that prepare for code changes.
* Command system was refactored so if you have custom commands or hook to that prepare for tears.
* [Gaufrette](https://github.com/KnpLabs/Gaufrette) support in storage. If you are using either GridFS or a local
  filesystem you could switch to this implementation if you wanted.

## From version 0.7.x to version 0.9.x

This upgrade is going to be fun as we did a major refactoring round. The whole structure of Filelib
was changed. When I do an actual migration for an actual client code, I will write this guide. :)

### PostgreSQL

    ALTER TABLE xi_filelib_resource RENAME COLUMN versions TO data;
    ALTER TABLE xi_filelib_file RENAME COLUMN versions TO data;
    ALTER TABLE xi_filelib_file DROP COLUMN filelink;

### MySQL

    ALTER TABLE xi_filelib_resource CHANGE versions data longtext NOT NULL;
    ALTER TABLE xi_filelib_file CHANGE versions data longtext NOT NULL;
    ALTER TABLE xi_filelib_file DROP COLUMN filelink;

### MongoDB

    db.files.update( { }, { $rename : { "versions" : "data" } }, false, true );
    db.resources.update( { }, { $rename : { "versions" : "data" } }, false, true );

    db.files.update( { }, { $unset: { link: "" } }, { multi: true });

## From version 0.6.x to version 0.7.x

The upgrade has a couple steps. It might be wise to backup your data (db + files) put your site to maintenance
mode when upgrading your production environment.

Make sure that all your files are uploaded succesfully and have status as 2.

### Refactor your custom plugins

Your custom plugins must be fixed. Make sure they are ready for the upgrade.

### Update code

Upgrade your code. Everything will be broken now. Faaaantastic!

### First backend upgrade batch

#### PostgreSQL

    CREATE TABLE xi_filelib_resource (id INT NOT NULL, hash VARCHAR(255) NOT NULL, mimetype VARCHAR(255) NOT NULL, filesize INT NOT NULL, exclusive BOOLEAN NOT NULL, date_created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, versions TEXT NOT NULL, PRIMARY KEY(id));
    INSERT INTO xi_filelib_resource (id, hash, mimetype, filesize, exclusive, date_created, versions) SELECT id, 'xooxoo', mimetype, filesize, false, date_uploaded, 'a:0:{}' FROM xi_filelib_file;
    CREATE SEQUENCE xi_filelib_resource_id_seq INCREMENT BY 1 MINVALUE 1 START 10000;

    ALTER TABLE xi_filelib_file RENAME COLUMN date_uploaded TO date_created;

    ALTER TABLE xi_filelib_folder ADD COLUMN uuid VARCHAR(36) NULL;
    CREATE UNIQUE INDEX UNIQ_A5EA9E8BD17F50A6 ON xi_filelib_folder (uuid);

    ALTER TABLE xi_filelib_file ADD COLUMN resource_id INT NULL;
    ALTER TABLE xi_filelib_file ADD COLUMN uuid VARCHAR(36) NULL;
    CREATE UNIQUE INDEX UNIQ_E8606524D17F50A6 ON xi_filelib_file (uuid);
    ALTER TABLE xi_filelib_file ADD COLUMN versions TEXT NOT NULL DEFAULT '';
    ALTER TABLE xi_filelib_file DROP COLUMN filesize;
    ALTER TABLE xi_filelib_file DROP COLUMN mimetype;

    UPDATE xi_filelib_file SET resource_id = id;
    UPDATE xi_filelib_file SET versions = 'a:0:{}';
    ALTER TABLE xi_filelib_file ALTER COLUMN resource_id SET NOT NULL;
    CREATE INDEX IDX_E860652489329D25 ON xi_filelib_file (resource_id);
    ALTER TABLE xi_filelib_file ADD CONSTRAINT FK_E860652489329D25 FOREIGN KEY (resource_id) REFERENCES xi_filelib_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE;

#### MySQL

    CREATE TABLE xi_filelib_resource (id INT AUTO_INCREMENT NOT NULL, hash VARCHAR(255) NOT NULL, mimetype VARCHAR(255) NOT NULL, filesize INT NOT NULL, exclusive TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, versions LONGTEXT NOT NULL COMMENT '(DC2Type:array)', PRIMARY KEY(id)) ENGINE = InnoDB;
    INSERT INTO xi_filelib_resource (id, hash, mimetype, filesize, exclusive, date_created, versions) SELECT id, 'xooxoo', mimetype, filesize, false, date_uploaded, 'a:0:{}' FROM xi_filelib_file;

    ALTER TABLE xi_filelib_file CHANGE date_uploaded date_created DATETIME NOT NULL;

    ALTER TABLE xi_filelib_folder ADD COLUMN uuid VARCHAR(36) NULL;
    CREATE UNIQUE INDEX UNIQ_A5EA9E8BD17F50A6 ON xi_filelib_folder (uuid);

    ALTER TABLE xi_filelib_file ADD COLUMN resource_id INT NULL;
    ALTER TABLE xi_filelib_file ADD COLUMN uuid VARCHAR(36) NULL;
    CREATE UNIQUE INDEX UNIQ_E8606524D17F50A6 ON xi_filelib_file (uuid);

    ALTER TABLE xi_filelib_file ADD COLUMN versions TEXT NOT NULL DEFAULT '';
    ALTER TABLE xi_filelib_file DROP COLUMN filesize;
    ALTER TABLE xi_filelib_file DROP COLUMN mimetype;

    UPDATE xi_filelib_file SET resource_id = id;
    UPDATE xi_filelib_file SET versions = 'a:0:{}';


    ALTER TABLE xi_filelib_file CHANGE resource_id resource_id INT NOT NULL;
    CREATE INDEX IDX_E860652489329D25 ON xi_filelib_file (resource_id);
    ALTER TABLE xi_filelib_file ADD FOREIGN KEY (resource_id) REFERENCES xi_filelib_resource (id);

#### MongoDB

    db.files.update( { }, { $rename : { "date_uploaded" : "date_created" } }, false, true );

    db.resources.drop();
    var createResource = function(file) {

        var resource = {
            _id: file._id,
            hash: "xooxoo",
            mimetype: file.mimetype,
            size: file.size,
            exclusive: false,
            date_created: file.date_created,
            versions: []
        };

        printjson(resource);
        db.resources.save(resource);

    };

    db.files.find().forEach(createResource);

    var fixFile = function(file) {

        file.resource_id = file._id.valueOf();
        delete file.mimetype;
        delete file.size;
        file.versions = [];

        printjson(file);
        db.files.save(file);

    };
    db.files.find().forEach(fixFile);

### Execute the migration command

Make sure the migration class, Xi\Filelib\Migration\ResourceRefactorMigration, is instantiated and executed
succesfully. If not, debug and run until it's good (it may be run and rerun).

- MongoDB migration outputs some errors concerning uuid. Do not care. It Just Works(tm).
- The symfony bundle has a console command for the migration (filelib:create_hashes_for_resources)

### Second backend upgrade batch

#### PostgreSQL

The start of the sequence must be at least MAX(id) FROM xi_filelib_resource + 1

    ALTER TABLE xi_filelib_file ALTER COLUMN uuid SET NOT NULL;
    ALTER TABLE xi_filelib_folder ALTER COLUMN uuid SET NOT NULL;
    CREATE SEQUENCE xi_filelib_resource_id_seq INCREMENT BY 1 MINVALUE 1 START 10000;

#### MySQL

    ALTER TABLE xi_filelib_file CHANGE uuid uuid varchar(36) NOT NULL;
    ALTER TABLE xi_filelib_folder CHANGE uuid uuid varchar(36) NOT NULL;

### Enjoy the results

Everything should work now!!!


## From version 0.5.x to version 0.6.x

Files now have a status field. You have to update your metadata backends
correspondingly.

### MongoDB

    db.files.update({}, { $set: { 'status': 2 }}, false, true);

### PostgreSQL

    ALTER TABLE xi_filelib_file ADD COLUMN status integer NOT NULL default 2;
    ALTER TABLE xi_filelib_file ALTER COLUMN status SET DEFAULT null;

### MySQL

    ALTER TABLE xi_filelib_file ADD COLUMN status integer NOT NULL default 2;
    ALTER TABLE xi_filelib_file ALTER COLUMN status DROP DEFAULT;

### Other SQL

Emulate previous example. Do what you must do.

## From versions < 0.5 to 0.5.x

Migrations are not officially supported. It is possible, though. You have
to update database tables to schema expected in 0.5.x series.

You also have to update Filelib sources and all integration packages. You may
still be using some Emerald\ prefixed software. They are not supported
and will not work with Filelib version 0.5.x.
