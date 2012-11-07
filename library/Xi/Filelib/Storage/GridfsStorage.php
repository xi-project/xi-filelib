<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use MongoDB;
use MongoGridFS;
use MongoGridFSFile;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Storage\AbstractStorage;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;

/**
 * Stores files in MongoDB's GridFS filesystem
 *
 * @author pekkis
 */
class GridfsStorage extends AbstractStorage implements Storage
{
    /**
     * @var string
     */
    private $tempDir;

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
     * @param  MongoDB       $mongo   A MongoDB instance
     * @param  string        $tempDir Temporary directory
     * @return GridfsStorage
     */
    public function __construct(MongoDB $mongo, $tempDir, $options = array())
    {
        $this->setMongo($mongo);
        $this->tempDir = $tempDir;
        parent::__construct($options);
    }

    /**
     * Deletes all temp files on destruct
     */
    public function __destruct()
    {
        foreach ($this->tempFiles as $tempFile) {
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
        if (!$this->gridFs) {
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


    public function exists(Resource $resource)
    {
        $filename = $this->getFilename($resource);
        $file = $this->getGridFS()->findOne(array('filename' => $filename));
        return (bool) $file;
    }


    public function versionExists(Resource $resource, $version, File $file = null)
    {
        $filename = $this->getFilenameVersion($resource, $version, $file);
        $file = $this->getGridFS()->findOne(array('filename' => $filename));
        return (bool) $file;
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
        $tmp = tempnam($this->tempDir, 'filelib');

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

    protected function doStore(Resource $resource, $tempFile)
    {
        $filename = $this->getFilename($resource);
        $this->getGridFS()->storeFile($tempFile, array('filename' => $filename, 'metadata' => array('id' => $resource->getId(), 'version' => 'original')));
    }

    protected function doStoreVersion(Resource $resource, $version, $tempFile, File $file = null)
    {
        $filename = $this->getFilenameVersion($resource, $version, $file);
        $this->getGridFS()->storeFile($tempFile, array('filename' => $filename, 'metadata' => array('id' => $resource->getId(), 'version' => $version)));
    }

    protected function doRetrieve(Resource $resource)
    {
        $filename = $this->getFilename($resource);
        $file = $this->getGridFS()->findOne(array('filename' => $filename));
        return $this->toTemp($file);
    }

    protected function doRetrieveVersion(Resource $resource, $version, File $file = null)
    {
        $filename = $this->getFilenameVersion($resource, $version, $file);
        $file = $this->getGridFS()->findOne(array('filename' => $filename));
        return $this->toTemp($file);
    }

    protected function doDelete(Resource $resource)
    {
        $filename = $this->getFilename($resource);
        $this->getGridFS()->remove(array('filename' => $filename));
    }

    protected function doDeleteVersion(Resource $resource, $version, File $file = null)
    {
        $filename = $this->getFilenameVersion($resource, $version, $file);
        $this->getGridFS()->remove(array('filename' => $filename));
    }

    private function getFilename(Resource $resource)
    {
        return $resource->getId();
    }

    private function getFilenameVersion(Resource $resource, $version, File $file = null)
    {
        $path = $resource->getId() . '/' . $version;
        if ($file) {
            $path = '/' . $file->getId();
        }
        return $path;
    }

}
