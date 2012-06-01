<?php

namespace Xi\Filelib\Storage;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Storage\AbstractStorage;
use Xi\Filelib\File\File;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator;
use Zend\Service\Amazon\S3\S3 as AmazonService;

class AmazonS3Storage extends AbstractStorage implements Storage
{
    
    
    private $amazonService;
        
    private $bucket;
    
    private $key;
    
    private $secretKey;
    
    /**
     * @var array Registered temporary files
     */
    private $tempFiles = array();
    
    /**
     * Deletes all temp files on destruct
     */
    public function __destruct()
    {
        foreach($this->tempFiles as $tempFile) {
            unlink($tempFile->getPathname());
        }
    }
    
    /**
     * @return \Zend_Service_Amazon_S3
     */
    public function getAmazonService()
    {
        if(!$this->amazonService) {
            $this->amazonService = new AmazonService($this->getKey(), $this->getSecretKey());
        }
        
        if(!$this->amazonService->isBucketAvailable($this->getBucket())) {
            $this->amazonService->createBucket($this->getBucket());
        }
        
        return $this->amazonService;
        
    }
    
    
    public function setBucket($bucket)
    {
        $this->bucket = $bucket;
    }
    
    
    public function getBucket()
    {
        return $this->bucket;
    }
    
    
    
    public function setKey($key)
    {
        $this->key = $key;
    }
    
    
    public function getKey()
    {
        return $this->key;
    }
    
    
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }
        
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    
    public function getPath($file)
    {
        return $this->getBucket() . '/' . $file->getId();
    }
        
    /**
     * Stores an uploaded file
     * 
     * @param File $file
     */
    public function store(File $file, $tempFile)
    {
        $object = $this->getPath($file);
        $this->getAmazonService()->putFile($tempFile, $object);
    }
    
    /**
     * Stores a version of a file
     * 
     * @param File $file
     * @param string $version
     * @param unknown_type $tempFile File to be stored
     */
    public function storeVersion(File $file, $version, $tempFile)
    {
        $object = $this->getPath($file) . '_' . $version;
        $this->getAmazonService()->putFile($tempFile, $object);
    }
    
    /**
     * Retrieves a file and temporarily stores it somewhere so it can be read.
     * 
     * @param File $file
     * @return FileObject
     */
    public function retrieve(File $file)
    {
        $object = $this->getPath($file);
        $ret = $this->getAmazonService()->getObject($object);
        return $this->toTemp($ret);        
    }
    
    /**
     * Retrieves a version of a file and temporarily stores it somewhere so it can be read.
     * 
     * @param File $file
     * @param string $version
     * @return FileObject
     */
    public function retrieveVersion(File $file, $version)
    {
        $object = $this->getPath($file) . '_' . $version;
        $ret = $this->getAmazonService()->getObject($object);
        return $this->toTemp($ret);        
    }
    
    /**
     * Deletes a file
     * 
     * @param File $file
     */
    public function delete(File $file)
    {
        $object = $this->getPath($file);
        $this->getAmazonService()->removeObject($object);
    }
    
    /**
     * Deletes a version of a file
     * 
     * @param File $file
     * @param string $version
     */
    public function deleteVersion(File $file, $version)
    {
        $object = $this->getPath($file) . '_' . $version;
        $this->getAmazonService()->removeObject($object);
    }
    
    
    private function toTemp($file)
    {
        $tmp = $this->getFilelib()->getTempDir() . '/' . tmpfile();
        
        file_put_contents($tmp, $file);
        
        $fo = new FileObject($tmp);
        
        $this->registerTempFile($fo);
        
        return $fo;
        
    }
    
    /**
     * Registers an internal temp file
     * 
     * @param FileObject $fo
     */
    private function registerTempFile(FileObject $fo)
    {
        $this->tempFiles[] = $fo;
    }
    
    
    
    
}