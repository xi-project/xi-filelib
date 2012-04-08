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