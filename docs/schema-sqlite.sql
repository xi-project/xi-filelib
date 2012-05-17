CREATE TABLE xi_filelib_file (id INTEGER NOT NULL, folder_id INTEGER NOT NULL, mimetype VARCHAR(255) NOT NULL, fileprofile VARCHAR(255) NOT NULL, filesize INTEGER DEFAULT NULL, filename VARCHAR(255) NOT NULL, filelink VARCHAR(255) DEFAULT NULL, date_uploaded DATETIME NOT NULL, status INTEGER NOT NULL, PRIMARY KEY("id"));
CREATE UNIQUE INDEX UNIQ_E860652454840E92 ON xi_filelib_file (filelink);
CREATE INDEX IDX_E8606524162CB942 ON xi_filelib_file (folder_id);
CREATE UNIQUE INDEX folderid_filename_unique ON xi_filelib_file (folder_id, filename);
CREATE TABLE xi_filelib_folder (id INTEGER NOT NULL, parent_id INTEGER DEFAULT NULL, foldername VARCHAR(255) NOT NULL, folderurl CLOB NOT NULL, PRIMARY KEY("id"));
CREATE INDEX IDX_A5EA9E8B727ACA70 ON xi_filelib_folder (parent_id);
