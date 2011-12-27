<?php

namespace Xi\Filelib\Backend;

use \MongoDb, \MongoId, \MongoDate, \DateTime;

/**
 * MongoDB backend for Filelib
 * 
 * @author pekkis
 * @package Xi_Filelib
 * @todo Prototype, to be error-proofed
 *
 */
class MongoBackend extends AbstractBackend implements Backend
{
    
    /**
     * MongoDB reference
     * 
     * @var \MongoDB
     */
    private $_mongo;
    
    /**
     * Sets MongoDB
     * 
     * @param \MongoDB $mongo
     */
    public function setMongo(\MongoDB $mongo)
    {
        $this->_mongo = $mongo;
    }
    
    
    /**
     * Returns MongoDB
     * 
     * @return \MongoDB
     */
    public function getMongo()
    {
        return $this->_mongo;
    }
    
    
    /**
     * Finds folder
     *
     * @param integer $id
     * @return \Xi\Filelib\Folder\Folder|false
     */
    public function findFolder($id)
    {
        $mongo = $this->getMongo();
                
        $doc = $mongo->folders->findOne(array('_id' => new MongoId($id)));
        
        if(!$doc) {
            return false;
        }
        
        $this->_mongoToFilelib($doc);    
                
        return $doc;
    }

    /**
     * Finds subfolders of a folder
     *
     * @param \Xi\Filelib\Folder\Folder $id
     * @return \Xi\Filelib\Folder\FolderIterator
     */
    public function findSubFolders(\Xi\Filelib\Folder\Folder $folder)
    {
        $mongo = $this->getMongo();

        $res = $mongo->folders->find(array('parent_id' => $folder->getId()));

        $ret = array();
        
        foreach($res as $row) {
            $this->_mongoToFilelib($row);
            $ret[] = $row;
        }
        
        return $ret;
                
    }
    

    /**
     * Finds all files
     *
     * @return \Xi\Filelib\File\FileIterator
     */
    public function findAllFiles()
    {
        $mongo = $this->getMongo();

        $res = $mongo->files->find();
        
        $files = array();
        
        foreach($res as $row) {

            $file = $row;
            $this->_mongoToFilelib($file);
            $files[] = $file;
        }
                
        return $files;        
    }
    

    /**
     * Finds a file
     *
     * @param integer $id
     * @return \Xi\Filelib\File\File|false
     */
    public function findFile($id)
    {
        $mongo = $this->getMongo();
                
        $file = $mongo->files->findOne(array('_id' => new MongoId($id)));

        if(!$file) {
            return false;
        }
                
        $this->_mongoToFilelib($file);    
        return $file;
    }
    
    /**
     * Finds a file
     *
     * @param \Xi\Filelib\Folder\Folder $folder
     * @return \Xi\Filelib\File\FileIterator
     */
    public function findFilesIn(\Xi\Filelib\Folder\Folder $folder)
    {
        $mongo = $this->getMongo();

        $res = $mongo->files->find(array('folder_id' => $folder->getId()));
        
        $files = array();

        foreach($res as $row) {

            $file = $row;
            $this->_mongoToFilelib($file);
            $files[] = $file;
        }
                
        return $files;        
    }
    
    /**
     * Uploads a file
     *
     * @param \Xi\Filelib\File\Upload\FileUpload $upload Fileobject to upload
     * @param \Xi\Filelib\Folder\Folder $folder Folder
     * @return \Xi\Filelib\File\File File item
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function upload(\Xi\Filelib\File\Upload\FileUpload $upload, \Xi\Filelib\Folder\Folder $folder, \Xi\Filelib\File\FileProfile $profile)
    {
        try {

            $file = array();

            $file['folder_id'] = $folder->getId();
            $file['mimetype'] = $upload->getMimeType();
            $file['size'] = $upload->getSize();
            $file['name'] = $upload->getOverrideFilename();
            $file['profile'] = $profile->getIdentifier();
            $file['date_uploaded'] = new MongoDate($upload->getDateUploaded()->getTimestamp()); 
                
            $this->getMongo()->files->insert($file);
            
            $this->getMongo()->files->ensureIndex(array('folder_id' => 1, 'name' => 1), array('unique' => true));
                       
            $this->_mongoToFilelib($file);
                        
            return $file;
            

        } catch(Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }
    	
    	
    }
    
    /**
     * Creates a folder
     *
     * @param \Xi\Filelib\Folder\Folder $folder
     * @return \Xi\Filelib\Folder\Folder Created folder
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function createFolder(\Xi\Filelib\Folder\Folder $folder)
    {
    	$arr = $folder->toArray();
    	$this->getMongo()->folders->insert($arr);
    	$this->getMongo()->folders->ensureIndex(array('name' => 1), array('unique' => true));
    	
    	$folder->setId($arr['_id']->__toString());
    	    	
    	return $folder;
    	
    }
    

    /**
     * Deletes a folder
     *
     * @param \Xi\Filelib\Folder\Folder $folder
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function deleteFolder(\Xi\Filelib\Folder\Folder $folder)
    {
        $this->getMongo()->folders->remove(array('_id' => new MongoId($folder->getId())));
    
    }
    
    /**
     * Deletes a file
     *
     * @param \Xi\Filelib\File\File $file
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function deleteFile(\Xi\Filelib\File\File $file)
    {
        $this->getMongo()->files->remove(array('_id' => new MongoId($file->getId())));
    }
    
    /**
     * Updates a folder
     *
     * @param \Xi\Filelib\Folder\Folder $folder
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function updateFolder(\Xi\Filelib\Folder\Folder $folder)
    {
    	$arr = $folder->toArray();
        $this->_filelibToMongo($arr);
    	
        $this->getMongo()->folders->update(array('_id' => new MongoId($folder->getId())), $arr);
        
        return $folder;
        
        
    }
    
    /**
     * Updates a file
     *
     * @param \Xi\Filelib\File\File $file
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function updateFile(\Xi\Filelib\File\File $file)
    {
        $arr = $file->toArray();
        $this->_filelibToMongo($arr);
                
        $this->getMongo()->files->update(array('_id' => new MongoId($file->getId())), $arr);
        return $file;
        
    }
    

        
    /**
     * Finds the root folder
     *
     * @return \Xi\Filelib\Folder\Folder
     */
    public function findRootFolder()
    {
        $mongo = $this->getMongo();
        
        $root = $mongo->folders->findOne(array('parent_id' => null));
        
        if(!$root) {

            $root = array(
                'parent_id' => null,
                'name' => 'root',
                'url' => '',
            );
            
            $mongo->folders->save($root);
                        
        }
        
                            
       $this->_mongoToFilelib($root);
       return $root;
    }
    
    
    /**
     * Finds folder by url
     *
     * @param  integer                          $id
     * @return \Xi\Filelib\Folder\Folder|false
     */
    public function findFolderByUrl($url)
    {
        $mongo = $this->getMongo();
        $folder = $mongo->folders->findOne(array('url' => $url));
        
        if(!$folder) {
            return false;
        }

        $this->_mongoToFilelib($folder);
        return $folder;
        
    }
        
    /**
     * Finds file in a folder by filename
     * 
     * @param unknown_type $folder
     * @param unknown_type $filename
     */
    public function findFileByFilename(\Xi\Filelib\Folder\Folder $folder, $filename)
    {
        $mongo = $this->getMongo();
        $file = $mongo->files->findOne(array(
            'folder_id' => $folder->getId(),
            'name' => $filename,
        ));
                
        if (!$file) {
            return false;
        }

        $this->_mongoToFilelib($file);
        return $file;
        
    }
    
    
    
    
    
    /**
     * Processes mongo data to fit Filelib requirements
     * 
     * @param array $data
     */
    private function _mongoToFilelib(array &$data)
    {
        $data['id'] = $data['_id']->__toString();
        
        if(isset($data['date_uploaded'])) {
            $data['date_uploaded'] = DateTime::createFromFormat('U', $data['date_uploaded']->sec);    
        }
        
    }
    
    
    /**
     * Processes Filelib data to fit Mongo requirements
     * 
     * @param array $data
     */
    private function _filelibToMongo(array &$data)
    {
        unset($data['id']);
        if(isset($data['date_uploaded'])) {
            $data['date_uploaded'] = new MongoDate($data['date_uploaded']->getTimestamp());    
        }
    }
    
    
    
    
    
    
    
}