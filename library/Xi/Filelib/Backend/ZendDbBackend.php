<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib, \DateTime;

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
        if(!$this->_db) {
            throw new FilelibException('Db handler has no db');
        }

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





    public function createFolder(\Xi\Filelib\Folder\Folder $folder)
    {
        try {
            $folderRow = $this->getFolderTable()->createRow();
            $folderRow->foldername = $folder->getName();
            $folderRow->parent_id = $folder->getParentId();
            $folderRow->folderurl = $folder->getUrl();            
                        
            $folderRow->save();
            	
            $folder->setId($folderRow->id);
            return $folder;
            	
        } catch(Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }


    }


    public function deleteFolder(\Xi\Filelib\Folder\Folder $folder)
    {
        try {
            $this->getFolderTable()->delete($this->getFolderTable()->getAdapter()->quoteInto("id = ?", $folder->getId()));
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
            $this->getFolderTable()->update(
                $data,
                $this->getFolderTable()->getAdapter()->quoteInto('id = ?', $folder->getId())
            );
            	
        } catch(Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }

    }


    public function updateFile(\Xi\Filelib\File\File $file)
    {
        try {

            $file->setLink($file->getProfileObject()->getLinker()->getLink($file, true));
            
            $data = array(
                'id' => $file->getId(),
                'folder_id' => $file->getFolderId(),
                'mimetype' => $file->getMimetype(),
                'filesize' => $file->getSize(),
                'filename' => $file->getName(),
                'fileprofile' => $file->getProfile(),
                'date_uploaded' => $file->getDateUploaded()->format('Y-m-d H:i:s'),
                'filelink' => $file->getLink(),
            ); 
            
            
            $this->getFileTable()->update(
                $data,
                $this->getFileTable()->getAdapter()->quoteInto('id = ?', $file->getId())
            );

            // $file->link = $file->link;
            	
        } catch(Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }

    }


    public function deleteFile(\Xi\Filelib\File\File $file)
    {
        try {
            $this->getDb()->beginTransaction();
            $fileRow = $this->getFileTable()->find($file->getId())->current();
            $fileRow->delete();
            $this->getDb()->commit();
            return true;
        } catch(Exception $e) {
            $this->getDb()->rollBack();
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }

    }

    public function upload(\Xi\Filelib\File\Upload\FileUpload $upload, \Xi\Filelib\Folder\Folder $folder, \Xi\Filelib\File\FileProfile $profile)
    {
        try {
                        
            $this->getDb()->beginTransaction();

            $file = $this->getFileTable()->createRow();
            
            $file->folder_id = $folder->getId();
            $file->mimetype = $upload->getMimeType();
            $file->filesize = $upload->getSize();
            $file->filename = $upload->getUploadFilename();
            $file->fileprofile = $profile->getIdentifier();
            $file->date_uploaded = $upload->getDateUploaded()->format('Y-m-d H:i:s');
                        	
            $file->save();
            	
            $this->getDb()->commit();
            	
            $ret = $this->_fileRowToArray($file);
            $ret['date_uploaded'] = new \DateTime($ret['date_uploaded']);
            
            return $ret;

        } catch(Exception $e) {
            	
            $this->getDb()->rollBack();
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }
        	
        	
    }


    public function findFolder($id)
    {
        $row = $this->getFolderTable()->find($id)->current();

        if(!$row) {
            return false;
        }

        return $this->_folderRowToArray($row);
                
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



    public function findSubFolders(\Xi\Filelib\Folder\Folder $folder)
    {
        $folderRows = $this->getFolderTable()->fetchAll(array('parent_id = ?' => $folder->getId()));
        
        $ret = array();
        foreach($folderRows as $folderRow) {
            $ret[] = $this->_folderRowToArray($folderRow);
        }
        
        return $ret;
    }



    public function findFile($id)
    {
        $fileRow = $this->getFileTable()->find($id)->current();
        if(!$fileRow) {
            return false;
        }
        
        $ret = $this->_fileRowToArray($fileRow);
        $ret['date_uploaded'] = new \DateTime($ret['date_uploaded']);
        return $ret;
        
    }


    public function findFilesIn(\Xi\Filelib\Folder\Folder $folder)
    {
        $res = $this->getFileTable()->fetchAll(array('folder_id = ?' => $folder->getId()));
       
        $ret = array();
        foreach($res as $awww) {
            $ret[] = $this->_fileRowToArray($awww);
        }
                
        array_walk($ret, function(&$ret) {
            $ret['date_uploaded'] = new DateTime($ret['date_uploaded']); 
        });      
        return $ret;
    }


    public function findAllFiles()
    {
        $res = $this->getFileTable()->fetchAll(array(), "id ASC");
        
        $ret = array();
        foreach($res as $awww) {
            $ret[] = $this->_fileRowToArray($awww);
        }
                
        array_walk($ret, function(&$ret) {
            $ret['date_uploaded'] = new DateTime($ret['date_uploaded']); 
        });      
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
        $folder = $this->getFolderTable()->fetchRow(array('folderurl = ?' => $url));
        
        if(!$folder) {
            return false;
        }
                
        return $this->_folderRowToArray($folder);
        
    }
        
    /**
     * Finds file in a folder by filename
     * 
     * @param unknown_type $folder
     * @param unknown_type $filename
     */
    public function findFileByFilename(\Xi\Filelib\Folder\Folder $folder, $filename)
    {
        $file = $this->getFileTable()->fetchRow(array(
            'folder_id = ?' => $folder->getId(),
            'filename = ?' => $filename,
        ));
                
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
            'date_uploaded' => $row->date_uploaded,
        );
        
    }
    
    
    private function _folderRowToArray($row)
    {
        return array(
            'id' => $row->id,
            'parent_id' => $row->parent_id,
            'name' => $row->foldername,
            'url' => $row->folderurl,
        );
        
    }
    
    




}
?>