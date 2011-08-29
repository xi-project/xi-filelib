CREATE TABLE xi_filelib_folder (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  parent_id int(10) unsigned DEFAULT NULL,
  foldername varchar(255) NOT NULL,
  folderurl varchar(5000) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY parent_id_name (parent_id, foldername),
  UNIQUE KEY folderurl (folderurl),
  KEY parent_id (parent_id),
  CONSTRAINT filelib_folder_ibfk_1 FOREIGN KEY (parent_id) REFERENCES xi_filelib_folder (id) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE xi_filelib_file (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  folder_id int(10) unsigned NOT NULL,
  mimetype varchar(255) NOT NULL,
  fileprofile varchar(255) NOT NULL DEFAULT 'default',
  filesize int(11) DEFAULT NULL,
  filename varchar(255) NOT NULL,
  filelink varchar(1000) DEFAULT NULL,
  date_uploaded datetime NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (filename,folder_id),
  KEY folder_id (folder_id),
  KEY mimetype (mimetype),
  CONSTRAINT filelib_file_ibfk_1 FOREIGN KEY (folder_id) REFERENCES xi_filelib_folder (id) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
