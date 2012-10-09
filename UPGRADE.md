# Filelib migrations

## From versions < 0.5 to 0.5.x

Migrations are not officially supported. It is possible, though. You have
to update database tables to schema expected in 0.5.x series.

You also have to update Filelib sources and all integration packages. You may
still be using some Emerald\ prefixed software. They are not supported
and will not work with Filelib version 0.5.x.

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

    db.files.update( { }, { $rename : { "date_uploaded" : "date_created" } } );

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

        file.resource_id = file._id.toString();
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

Everything should work now!
