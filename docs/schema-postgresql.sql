CREATE SEQUENCE xi_filelib_folder_id_seq;

CREATE TABLE "xi_filelib_folder" (
  "id" int  NOT NULL DEFAULT NEXTVAL('xi_filelib_folder_id_seq'),
  "parent_id" int  DEFAULT NULL,
  "foldername" varchar(255) NOT NULL,
  "folderurl" varchar(5000) NOT NULL,
  PRIMARY KEY ("id"),
  UNIQUE ("parent_id","foldername"),
  UNIQUE("folderurl"),
  FOREIGN KEY ("parent_id") REFERENCES "xi_filelib_folder" ("id") ON DELETE NO ACTION ON UPDATE CASCADE
);

CREATE SEQUENCE xi_filelib_file_id_seq;

CREATE TABLE "xi_filelib_file" (
  "id" int  NOT NULL DEFAULT NEXTVAL('xi_filelib_file_id_seq'),
  "folder_id" int  NOT NULL,
  "mimetype" varchar(255) NOT NULL,
  "fileprofile" varchar(255) NOT NULL DEFAULT 'default',
  "filesize" int DEFAULT NULL,
  "filename" varchar(255) NOT NULL,
  "filelink" varchar(1000) DEFAULT NULL,
  "date_uploaded" timestamp NOT NULL,
  PRIMARY KEY ("id"),
  UNIQUE ("filename","folder_id"),
  FOREIGN KEY ("folder_id") REFERENCES "xi_filelib_folder" ("id") ON DELETE NO ACTION ON UPDATE CASCADE
);

