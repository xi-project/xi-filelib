<?php

namespace Xi\Filelib;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Plugin\Plugin;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Acl\Acl;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract convenience class for operators
 * 
 * @author pekkis
 * 
 */
abstract class AbstractOperator
{
    /**
     * Filelib reference
     * 
     * @var FileLibrary
     */
    protected $filelib;
    
    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }
    
    /**
     * Returns backend
     *
     * @return Backend
     */
    public function getBackend()
    {
        return $this->getFilelib()->getBackend();
    }

    
    /**
     * Returns storage
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->getFilelib()->getStorage();
    }
    
    /**
     * Returns publisher
     *
     * @return Publisher
     */
    public function getPublisher()
    {
        return $this->getFilelib()->getPublisher();
    }
    
    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->filelib;
    }

    
    /**
     * Returns Acl
     *
     * @return Acl
     */
    public function getAcl()
    {
        return $this->getFilelib()->getAcl();
    }

    
    /**
     * Returns Event dispatcher
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->getFilelib()->getEventDispatcher();
    }
    
    /**
     * Returns queue
     * 
     * @return Queue
     */
    public function getQueue()
    {
        return $this->getFilelib()->getQueue();
    }
    
    
    
     /**
     * Transforms raw array to folder item
     * @param array $data
     * @return Folder
     */
    protected function _folderItemFromArray(array $data)
    {
        return $this->getFilelib()->getFolderOperator()->getInstance($data);
    }
        
    /**
     * Transforms raw array to file item
     * @param array $data
     * @return File
     */
    protected function _fileItemFromArray(array $data)
    {
        return $this->getFilelib()->getFileOperator()->getInstance($data);
    }
    
    

}