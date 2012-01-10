<?php

namespace Xi\Filelib\File;

use \Xi\Filelib\File\FileOperator;
use \Xi\Filelib\AbstractOperator;
use \Xi\Filelib\FilelibException;


/**
 * Operates on files
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
class DefaultFileOperator extends AbstractOperator implements FileOperator
{
    /**
     * @var string
     */
    protected $_cachePrefix = 'xi_filelib_fileoperator';

    
    /**
     * @var array Profiles
     */
    private $_profiles = array();
    
    /**
     * @var string Fileitem class
     */
    private $_className = '\Xi\Filelib\File\FileItem';
    
    /**
     * Sets fileitem class
     *
     * @param string $className Class name
     * @return \Xi\Filelib\FileLibrary
     */
    public function setClass($className)
    {
        $this->_className = $className;
        return $this;
    }


    /**
     * Returns fileitem class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->_className;
    }
    
    
    /**
     * Returns an instance of the currently set fileitem class
     * 
     * @param mixed $data Data as array or a file instance
     */
    public function getInstance($data = null)
    {
        if($data instanceof File) {
            $data->setFilelib($this->getFilelib());
            return $data;
        }
        
        $className = $this->getClass();
        $file = new $className();
        $file->setFilelib($this->getFilelib());
        if($data) {
            $file->fromArray($data);   
        }
        return $file;        
    }
    
    /**
     * Adds a file profile
     * 
     * @param \Xi\Filelib\File\FileProfile $profile
     * @return \Xi\Filelib\FileLibrary
     */
    public function addProfile(FileProfile $profile)
    {
        $profile->setFilelib($this->getFilelib());
        $profile->getLinker()->setFilelib($this->getFilelib());

        if(!isset($this->_profiles[$profile->getIdentifier()])) {
            $this->_profiles[$profile->getIdentifier()] = $profile;
        }
        
        return $this;
    }

    /**
     * Returns a file profile
     * 
     * @param string $identifier File profile identifier
     * @throws \Xi\Filelib\FilelibException
     * @return \Xi\Filelib\File\FileProfile
     */
    public function getProfile($identifier)
    {
        if(!isset($this->_profiles[$identifier])) {
            throw new FilelibException("File profile '{$identifier}' not found");
        }

        return $this->_profiles[$identifier];
    }

    /**
     * Returns all file profiles
     * 
     * @return array Array of file profiles
     */
    public function getProfiles()
    {
        return $this->_profiles;
    }
    

    /**
     * Updates a file
     *
     * @param \Xi\Filelib\File\File $file
     * @return unknown_type
     */
    public function update(\Xi\Filelib\File\File $file)
    {
        $this->unpublish($file);        
        
        $file->setLink($file->getProfileObject()->getLinker()->getLink($file, true));
                
        $this->getBackend()->updateFile($file);

        $this->storeCached($file->getId(), $file);

        if($this->isReadableByAnonymous($file)) {
            $this->publish($file);
        }

        $this->storeCached($file->getId(), $file);
        
        return $this;

    }


    /**
     * Finds a file
     *
     * @param mixed $idFile File id
     * @return \Xi\Filelib\File\File
     */
    public function find($id)
    {
        if(!$file = $this->findCached($id)) {
            $file = $this->getBackend()->findFile($id);
        }
                    
        if(!$file) {
            return false;
        }

        $file = $this->_fileItemFromArray($file);
        $this->storeCached($file->getId(), $file);
        return $file;

    }
    
    
    public function findByFilename(\Xi\Filelib\Folder\Folder $folder, $filename)
    {
        $file = $this->getBackend()->findFileByFilename($folder, $filename);
        
        if(!$file) {
            return false;
        }

        $file = $this->_fileItemFromArray($file);

        return $file;
                
    }
    
    

    /**
     * Finds and returns all files
     *
     * @return \Xi\Filelib\File\FileIterator
     */
    public function findAll()
    {
        $ritems = $this->getBackend()->findAllFiles();

        $items = array();
        foreach($ritems as $ritem) {
            $item = $this->_fileItemFromArray($ritem);
            $items[] = $item;
        }
        return $items;
    }




    /**
     * Returns whether a file is anonymous
     *
     * @todo This is still mock!
     * @param \Xi\Filelib\File\File $file File
     * @return boolean
     */
    public function isReadableByAnonymous(\Xi\Filelib\File\File $file)
    {
        return $this->getFilelib()->getAcl()->isReadableByAnonymous($file);

    }


    /**
     * Gets a new upload
     *
     * @param string $path Path to upload file
     * @return \Xi\Filelib\File\Upload\FileUpload
     */
    public function prepareUpload($path)
    {
        $upload = new \Xi\Filelib\File\Upload\FileUpload($path);
        $upload->setFilelib($this->getFilelib());
        return $upload;
    }

    
    /**
     * Uploads many files at once
     * 
     * @param Iterator $batch Collection of \SplFileInfo objects
     * @return ArrayIterator Collection of uploaded file items
     */
    public function uploadBatch(\Iterator $batch, $folder, $profile = 'default')
    {
        if(!($folder instanceof \Xi\Filelib\Folder\Folder)) {
            throw new \Xi\Filelib\FilelibException('Invalid folder supplied for batch upload');
        }
        
        foreach ($batch as $item) {
            if (!($item instanceof \SplFileInfo)) {
                throw new \Xi\Filelib\FilelibException('Invalid upload detected in batch');
            }
        }
                
        $ret = new \ArrayIterator(array());
        foreach ($batch as $item) {
            if($item->isFile()) {
                $upload = $this->prepareUpload($item->getPathname());
                $uploaded = $this->upload($upload, $folder, $profile);
                $ret->append($uploaded);
            }
        }
        
        return $ret;
    }
    
    

    /**
     * Uploads file to filelib.
     *
     * @param mixed $upload Uploadable, path or object
     * @param \Xi\Filelib\Folder\Folder $folder
     * @return \Xi\Filelib\File\File
     * @throws \Xi\Filelib\FilelibException
     */
    public function upload($upload, $folder, $profile = 'default')
    {
        if(!($folder instanceof \Xi\Filelib\Folder\Folder)) {
            throw new \Xi\Filelib\FilelibException('Invalid folder supplied for upload');
        }
        
        if(!$upload instanceof \Xi\Filelib\File\Upload\FileUpload) {
            $upload = $this->prepareUpload($upload);
        }

        return $upload->upload($folder, $profile);
    }


    /**
     * Deletes a file
     *
     * @param \Xi\Filelib\File\File $file
     * @throws \Xi\Filelib\FilelibException
     */
    public function delete(\Xi\Filelib\File\File $file)
    {
        try {

            $this->unpublish($file);
            
            $this->getBackend()->deleteFile($file);
            $this->clearCached($file->getId());
            $this->getFilelib()->getStorage()->delete($file);

            foreach($file->getProfileObject()->getPlugins() as $plugin) {
                if($plugin instanceof \Xi\Filelib\Plugin\VersionProvider\VersionProvider && $plugin->providesFor($file)) {
                    $plugin->onDelete($file);
                }
            }
            	
            return true;
            	
        } catch(Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }

    }


    /**
     * Returns file type of a file
     *
     * @param \Xi\Filelib\File\File File $file item
     * @return string File type
     */
    public function getType(\Xi\Filelib\File\File $file)
    {
        // @todo Semi-mock until mimetype database is pooped in.
        $split = explode('/', $file->getMimetype());
        return $split[0];
    }


    /**
     * Returns whether a file has a certain version
     *
     * @param \Xi\Filelib\File\File $file File item
     * @param string $version Version
     * @return boolean
     */
    public function hasVersion(\Xi\Filelib\File\File $file, $version)
    {
        $filetype = $this->getType($file);
        $profile = $file->getProfileObject();
        if($profile->fileHasVersion($file, $version)) {
            return true;
        }
        return false;
    }


    /**
     * Returns version provider for a file/version
     *
     * @param \Xi\Filelib\File\File $file File item
     * @param string $version Version
     * @return object Provider
     */
    public function getVersionProvider(\Xi\Filelib\File\File $file, $version)
    {
        return $file->getProfileObject()->getVersionProvider($file, $version);
    }

    
    public function getUrl(\Xi\Filelib\File\File $file, $opts = array())
    {
        if(!$file->getId()) {
            throw new \Xi\Filelib\FilelibException('File has no id');
        }
        
        
        if (isset($opts['version']) && $opts['version'] !== 'original') {
            $version = $opts['version'];
            
            if(!$this->hasVersion($file, $version)) {
                throw new \Xi\Filelib\FilelibException("Version '{$version}' is not available", 404);
            }
            
            $provider = $this->getVersionProvider($file, $version);
            $url = $this->getFilelib()->getPublisher()->getUrlVersion($file, $provider);
                     
        } else {
                        
            if(!$file->getProfileObject()->getAccessToOriginal()) {
                throw new \Xi\Filelib\FilelibException("Access to the original file is not allowed", 403);
            }
            
            $url = $this->getFilelib()->getPublisher()->getUrl($file);
        }
        return $url;
    }
    

    /**
     * Renders a file to a response
     *
     * @param \Xi_Filelib File $file item
     * @param \Zend_Controller_Response_Http $response Response
     * @param array $opts Options
     */
    public function render(\Xi\Filelib\File\File $file, $opts = array())
    {
        if(!$file->getId()) {
            throw new \Xi\Filelib\FilelibException('File has no id');
        }
        
        if(!$this->getFilelib()->getAcl()->isReadable($file)) {
            throw new \Xi\Filelib\FilelibException('Not readable', 404);
        }
        
        if (isset($opts['version']) && $opts['version'] !== 'original') {
            $version = $opts['version'];
            if(!$this->hasVersion($file, $version)) {
                throw new \Xi\Filelib\FilelibException("Version '{$version}' is not available");
            }
            $provider = $this->getVersionProvider($file, $version);
            $res = $this->getFilelib()->getStorage()->retrieveVersion($file, $provider);
        } else {

            if(!$file->getProfileObject()->getAccessToOriginal()) {
                throw new \Xi\Filelib\FilelibException("Access to the original file is not allowed", 403);
            }
            
            $res = $this->getFilelib()->getStorage()->retrieve($file);
        }

        if(!is_readable($res->getPathname())) {
            throw new \Xi\Filelib\FilelibException('File not readable', 404);
        }

        return file_get_contents($res->getPathname());

    }

    
    public function publish(\Xi\Filelib\File\File $file)
    {
        if(!$file->getId()) {
            throw new \Xi\Filelib\FilelibException('File has no id');
        }
        
        if($file->getProfileObject()->getPublishOriginal()) {
            $this->getFilelib()->getPublisher()->publish($file);    
        }                
        
        foreach($file->getProfileObject()->getPlugins() as $plugin) {
            
            $plugin->onPublish($file);
            
        }
        
    }
    
    public function unpublish(\Xi\Filelib\File\File $file)
    {
        if(!$file->getId()) {
            throw new \Xi\Filelib\FilelibException('File has no id');
        }
        
        $this->getFilelib()->getPublisher()->unpublish($file);
        
        foreach($file->getProfileObject()->getPlugins() as $plugin) {
            $plugin->onUnpublish($file);
        }
        
    }

    
    
}