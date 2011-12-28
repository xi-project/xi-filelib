<?php

namespace Xi\Filelib\Storage;

use \MongoDB,
    \MongoGridFS,
    \MongoGridFSFile,
    \Xi\Filelib\FileLibrary,
    \Xi\Filelib\Storage\Storage,
    \Xi\Filelib\Storage\AbstractStorage,
    \Xi\Filelib\File\File,
    \Xi\Filelib\Configurator,
    \Xi\Filelib\File\FileObject,
    \Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator,
    \Xi\Filelib\Plugin\VersionProvider\VersionProvider
    ;


/**
 * Stores files in MongoDB's GridFS filesystem
 * 
 * @author pekkis
 *
 */
class GridfsStorage extends AbstractStorage implements Storage
{
    /**
     * @var MongoDB Mongo reference
     */
    private $mongo;
    
    /**
     * @var string Collection name
     */
    private $collection;
    
    /**
     * @var string GridFS prefix
     */
    private $prefix = 'xi_filelib';
    
    /**
     * @var MongoGridFS GridFS reference
     */
    private $gridFs;
    
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
     * Sets mongo
     * 
     * @param MongoDB $mongo
     */
    public function setMongo(MongoDB $mongo)
    {
        $this->mongo = $mongo;
    }
    
    /**
     * Returns mongo
     * 
     * @return MongoDB
     */
    public function getMongo()
    {
        return $this->mongo;
    }
    
    /**
     * Returns GridFS
     * 
     * @return \MongoGridFS
     */
    public function getGridFS()
    {
        if(!$this->gridFs) {
            $this->gridFs = $this->getMongo()->getGridFS($this->getPrefix());    
        }
        return $this->gridFs;
    }
    
    /**
     * Sets gridfs prefix
     * 
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
    
    /**
     * Returns gridfs prefix
     * 
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
    
    /**
     * Writes a mongo file to temporary file and registers it as an internal temp file
     * 
     * @param MongoGridFSFile $file
     * @return FileObject 
     * 
     */
    private function toTemp(MongoGridFSFile $file)
    {
        $tmp = $this->getFilelib()->getTempDir() . '/' . tmpfile();
        $file->write($tmp);
        
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
    
    public function store(File $file, $tempFile)
    {
        $filename = $this->getFilename($file);
        
        $this->getGridFS()->storeFile($tempFile, array('filename' => $filename, 'metadata' => array('id' => $file->getId(), 'version' => 'original', 'mimetype' => $file->getMimetype()) ));
    }
    
    public function storeVersion(File $file, VersionProvider $version, $tempFile)
    {
        $filename = $this->getFilenameVersion($file, $version);
        
        $this->getGridFS()->storeFile($tempFile, array('filename' => $filename, 'metadata' => array('id' => $file->getId(), 'version' => $version->getIdentifier(), 'mimetype' => $file->getMimetype()) ));
    }
    
    public function retrieve(File $file)
    {
        $filename = $this->getFilename($file);
        
        $file = $this->getGridFS()->findOne(array('filename' => $filename));

        if(!$file) {
            throw new FilelibException("Filename '{$filename}' not retrievable");
        }
        
        
        return $this->toTemp($file);
    }
    
    public function retrieveVersion(File $file, VersionProvider $version)
    {
        $filename = $this->getFilenameVersion($file, $version);
        
        $file = $this->getGridFS()->findOne(array('filename' => $filename));
        
        if(!$file) {
            throw new FilelibException("Filename '{$filename}' not retrievable");
        }
        
        
        return $this->toTemp($file);
    }
    
    public function delete(File $file)
    {
        $filename = $this->getFilename($file);
        
        $this->getGridFS()->remove(array('filename' => $filename));
    }
    
    public function deleteVersion(File $file, VersionProvider $version)
    {
        $filename = $this->getFilenameVersion($file, $version);
        
        $this->getGridFS()->remove(array('filename' => $filename));
    }
    
    
    public function getFilename(File $file)
    {
        return $file->getFolderId() . '/' . $file->getId();
    }
    
    public function getFilenameVersion(File $file, VersionProvider $version)
    {
        return $file->getFolderId() . '/' . $file->getId() . '/' . $version->getIdentifier();
    }
    
    
}