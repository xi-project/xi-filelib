<?php

namespace Xi\Filelib\Backend;

use \Xi\Filelib\FileLibrary,
    \Xi\Filelib\FilelibException,
    \DateTime,
    \Exception,
    \Xi\Filelib\File\File,
    \Xi\Filelib\Folder\Folder
    ;


/**
 * Zend Db backend for filelib.
 *
 * @package Xi_Filelib
 * @author pekkis
 *
 */
class ZendDbBackend extends AbstractBackend implements Backend
{

    /**
     * @var Zend_Db_Adapter_Abstract Zend Db adapter
     */
    private $_db;

    /**
     * @var \Xi\Filelib\Backend\ZendDb\FileTable File table
     */
    private $_fileTable;

    /**
     * @var \Xi\Filelib\Backend\ZendDb\FolderTable Folder table
     */
    private $_folderTable;


    /**
     * Sets db adapter
     *
     * @param \Zend_Db_Adapter_Abstract $db
     * @return unknown_type
     */
    public function setDb(\Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
    }


    /**
     * Returns db adapter
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDb()
    {
        return $this->_db;
    }

    
    /**
     * Returns file table
     *
     * @return \Xi\Filelib\Backend\ZendDb\FolderTable
     */
    public function getFileTable()
    {
        if(!$this->_fileTable) {
            $this->_fileTable = new \Xi\Filelib\Backend\ZendDb\FileTable($this->getDb());
        }
        return $this->_fileTable;
    }

    /**
     * Returns folder table
     *
     * @return \Xi\Filelib\Backend\ZendDb\FolderTable
     */
    public function getFolderTable()
    {
        if(!$this->_folderTable) {
            $this->_folderTable = new \Xi\Filelib\Backend\ZendDb\FolderTable($this->getDb());
        }

        return $this->_folderTable;
    }
   

    public function findRootFolder()
    {
        $row = $this->getFolderTable()->fetchRow(array('parent_id IS NULL'));
        
        if(!$row) {
            
            $row = $this->getFolderTable()->createRow();
            $row->foldername = 'root';
            $row->parent_id = null;
            $row->folderurl = '';
            $row->save();
            
        }
        
        return $this->_folderRowToArray($row);
    }
    
    
    public function findFolder($id)
    {
        if(!is_numeric($id)) {
            throw new FilelibException("File id must be numeric");
        }
        
        $row = $this->getFolderTable()->find($id)->current();

        if(!$row) {
            return false;
        }

        return $this->_folderRowToArray($row);
                
    }

    
    public function createFolder(\Xi\Filelib\Folder\Folder $folder)
    {
        try {
            $folderRow = $this->getFolderTable()->createRow();
            $folderRow->foldername = $folder->getName();
            $folderRow->parent_id = $folder->getParentId();
            $folderRow->folderurl = $folder->getUrl();            
                        
            $folderRow->save();
            	
            $folder->setId((int) $folderRow->id);
            return $folder;
            	
        } catch(Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }

    }

    
    public function deleteFolder(\Xi\Filelib\Folder\Folder $folder)
    {
        try {
            $ret = $this->getFolderTable()->delete($this->getFolderTable()->getAdapter()->quoteInto("id = ?", $folder->getId()));
            
            return (bool) $ret;
            
        } catch(Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }

    }
    
    public function updateFolder(\Xi\Filelib\Folder\Folder $folder)
    {
        $data = array(
            'id' => $folder->getId(),
            'parent_id' => $folder->getParentId(),
            'foldername' => $folder->getName(),
            'folderurl' => $folder->getUrl(),
        );
        
        try {
            $ret = $this->getFolderTable()->update(
                $data,
                $this->getFolderTable()->getAdapter()->quoteInto('id = ?', $folder->getId())
            );
            
            return (bool) $ret;
            
            	
        } catch(Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }

    }
    
    
    
    public function findSubFolders(\Xi\Filelib\Folder\Folder $folder)
    {
        try {
            $folderRows = $this->getFolderTable()->fetchAll(array('parent_id = ?' => $folder->getId()));
        } catch(Exception $e) {
            throw new FilelibException('Invalid folder');
        }
        
        $ret = array();
        foreach($folderRows as $folderRow) {
            $ret[] = $this->_folderRowToArray($folderRow);
        }
        
        return $ret;
    }
    
    
    /**
     * Finds folder by url
     *
     * @param  integer                          $id
     * @return \Xi\Filelib\Folder\Folder|false
     */
    public function findFolderByUrl($url)
    {
        try {
            $folder = $this->getFolderTable()->fetchRow(array('folderurl = ?' => $url));
        } catch(Exception $e) {
            throw new FilelibException($e->getMessage());
        }
        
        if(!$folder) {
            return false;
        }
                
        return $this->_folderRowToArray($folder);
        
    }
    
    public function findFilesIn(\Xi\Filelib\Folder\Folder $folder)
    {
        try {
            $res = $this->getFileTable()->fetchAll(array('folder_id = ?' => $folder->getId()));
        } catch(Exception $e) {
            throw new FilelibException($e->getMessage());
        }
       
        $ret = array();
        foreach($res as $awww) {
            $ret[] = $this->_fileRowToArray($awww);
        }
                
        return $ret;
    }

    
    public function findAllFiles()
    {
        $res = $this->getFileTable()->fetchAll(array(), "id ASC");
        
        $ret = array();
        foreach($res as $awww) {
            $ret[] = $this->_fileRowToArray($awww);
        }
        return $ret;
        
    }

    
    public function findFile($id)
    {
         try {
            $fileRow = $this->getFileTable()->find($id)->current();
            
            if (!$fileRow) {
                return false;
            }
            
            $ret = $this->_fileRowToArray($fileRow);
            
            return $ret;
            
        } catch(Exception $e) {
            throw new FilelibException($e->getMessage());
        }
                
        
    }
    
    
    

    public function updateFile(\Xi\Filelib\File\File $file)
    {
        try {
            
            $data = $file->toArray();
            
            $fixed = array(
                'folder_id' => $data['folder_id'],
                'mimetype' => $data['mimetype'],
                'filesize' => $data['size'],
                'filename' => $data['name'],
                'fileprofile' => $data['profile'],
                'date_uploaded' => $data['date_uploaded']->format('Y-m-d H:i:s'),
                'filelink' => $data['link'],
            ); 
            
            
            $ret = $this->getFileTable()->update(
                $fixed,
                $this->getFileTable()->getAdapter()->quoteInto('id = ?', $data['id'])
            );

            return (boolean) $ret;
            	
        } catch(Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }

    }


    public function deleteFile(\Xi\Filelib\File\File $file)
    {
        try {
            $this->getDb()->beginTransaction();
            $fileRow = $this->getFileTable()->find($file->getId())->current();
            
            if (!$fileRow) {
                return false;
            }
            
            $ret = $fileRow->delete();
            $this->getDb()->commit();
            return true;
            
        } catch(Exception $e) {
            $this->getDb()->rollBack();
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }

    }
    

    public function upload(File $file, \Xi\Filelib\Folder\Folder $folder)
    {
        try {
                        
            $this->getDb()->beginTransaction();

            $row = $this->getFileTable()->createRow();
            
            $row->folder_id = $folder->getId();
            $row->mimetype = $file->getMimeType();
            $row->filesize = $file->getSize();
            $row->filename = $file->getName();
            $row->fileprofile = $file->getProfile();
            $row->date_uploaded = $file->getDateUploaded()->format('Y-m-d H:i:s');
                        	
            $row->save();
                                    	
            $this->getDb()->commit();
            
            $file->setId($row->id);
            $file->setFolderId($row->folder_id);
            
            return $file;

        } catch(Exception $e) {
            	
            $this->getDb()->rollBack();
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }
        	
        	
    }


    
    
        
    /**
     * Finds file in a folder by filename
     * 
     * @param unknown_type $folder
     * @param unknown_type $filename
     */
    public function findFileByFilename(\Xi\Filelib\Folder\Folder $folder, $filename)
    {
        
        try {
            $file = $this->getFileTable()->fetchRow(array(
                'folder_id = ?' => $folder->getId(),
                'filename = ?' => $filename,
            ));
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
        
        if (!$file) {
            return false;
        }

        return $this->_fileRowToArray($file);
        
    }
        
    
    
    private function _fileRowToArray($row) 
    {
        return array(
            'id' => $row->id,
            'folder_id' => $row->folder_id,
            'mimetype' => $row->mimetype,
            'size' => $row->filesize,
            'name' => $row->filename,
            'profile' => $row->fileprofile,
            'link' => $row->filelink,
            'date_uploaded' => new DateTime($row->date_uploaded),
        );
        
    }
    
    
    private function _folderRowToArray($row)
    {
        return array(
            'id' => (int) $row->id,
            'parent_id' => (int) $row->parent_id,
            'name' => $row->foldername,
            'url' => $row->folderurl,
        );
        
    }
    
    




}
