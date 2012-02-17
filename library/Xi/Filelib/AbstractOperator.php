<?php

namespace Xi\Filelib;

/**
 * Base class for operators
 * 
 * @package Xi_Filelib
 * @author pekkis
 * 
 */
abstract class AbstractOperator
{
    /**
     * Filelib reference
     * 
     * @var \Xi\Filelib\FileLibrary
     */
    protected $_filelib;
    
    public function __construct(\Xi\Filelib\FileLibrary $filelib)
    {
        $this->_filelib = $filelib;
    }
    
    /**
     * Returns backend
     *
     * @return \Xi\Filelib\Backend\Backend
     */
    public function getBackend()
    {
        return $this->getFilelib()->getBackend();
    }

    /**
     * Returns filelib
     *
     * @return \Xi\Filelib\FileLibrary
     */
    public function getFilelib()
    {
        return $this->_filelib;
    }
    
     /**
     * Transforms raw array to folder item
     * @param array $data
     * @return \Xi\Filelib\Folder\Folder
     */
    protected function _folderItemFromArray(array $data)
    {
        return $this->getFilelib()->getFolderOperator()->getInstance($data);
    }
        
    /**
     * Transforms raw array to file item
     * @param array $data
     * @return null
     */
    protected function _fileItemFromArray(array $data)
    {
        return $this->getFilelib()->getFileOperator()->getInstance($data);
    }
    
    

}