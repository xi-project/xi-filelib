<?php

namespace Xi\Filelib\Storage;

use MongoDB;
use MongoGridFS;
use MongoGridFSFile;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Storage\AbstractStorage;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator;
use Xi\Filelib\FilelibException;


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
     * @param MongoGridFSFile $resource
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

    public function store(Resource $resource, $tempFile)
    {
        $filename = $this->getFilename($resource);

        $this->getGridFS()->storeFile($tempFile, array('filename' => $filename, 'metadata' => array('id' => $resource->getId(), 'version' => 'original')));
    }

    public function storeVersion(Resource $resource, $version, $tempFile, File $file = null)
    {
        $filename = $this->getFilenameVersion($resource, $version);

        $this->getGridFS()->storeFile($tempFile, array('filename' => $filename, 'metadata' => array('id' => $resource->getId(), 'version' => $version)));
    }

    public function retrieve(Resource $resource)
    {
        $filename = $this->getFilename($resource);

        $file = $this->getGridFS()->findOne(array('filename' => $filename));

        if(!$file) {
            throw new FilelibException("Filename '{$filename}' not retrievable");
        }


        return $this->toTemp($file);
    }

    public function retrieveVersion(Resource $resource, $version, File $file = null)
    {
        $filename = $this->getFilenameVersion($resource, $version);

        $file = $this->getGridFS()->findOne(array('filename' => $filename));

        if(!$file) {
            throw new FilelibException("Filename '{$filename}' not retrievable");
        }


        return $this->toTemp($file);
    }

    public function delete(Resource $resource)
    {
        $filename = $this->getFilename($resource);

        $this->getGridFS()->remove(array('filename' => $filename));
    }

    public function deleteVersion(Resource $resource, $version, File $file = null)
    {
        $filename = $this->getFilenameVersion($resource, $version);

        $this->getGridFS()->remove(array('filename' => $filename));
    }


    public function getFilename(Resource $resource)
    {
        return $resource->getId();
    }

    public function getFilenameVersion(Resource $resource, $version, File $file = null)
    {
        return $resource->getId() . '/' . $version;
    }


}