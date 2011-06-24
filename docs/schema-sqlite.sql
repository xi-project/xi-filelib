

CREATE TABLE emerald_filelib_folder(
id integer PRIMARY KEY AUTOINCREMENT,
parent_id integer NULL,
foldername varchar(255) NOT NULL,
folderurl varchar(5000) NOT NULL,
UNIQUE (parent_id, foldername),
UNIQUE(folderurl),
FOREIGN KEY(parent_id) REFERENCES emerald_filelib_folder (id) ON DELETE NO ACTION ON UPDATE CASCADE
);

CREATE TABLE emerald_filelib_file
(
  id integer  NOT NULL PRIMARY KEY AUTOINCREMENT,
  folder_id integer  NOT NULL,
  mimetype varchar(255) NOT NULL,
  fileprofile varchar(255) NOT NULL DEFAULT 'default',
  filesize integer DEFAULT NULL,
  filename varchar(255) NOT NULL,
  filelink varchar(1000) DEFAULT NULL,
  date_uploaded timestamp NOT NULL,
  UNIQUE (filename,folder_id),
  FOREIGN KEY (folder_id) REFERENCES emerald_filelib_folder (id) ON DELETE NO ACTION ON UPDATE CASCADE
);

