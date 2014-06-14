<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter;

use MongoDB;
use MongoGridFS;
use MongoGridFSFile;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Storage\Storable;

/**
 * Stores files in MongoDB's GridFS filesystem
 *
 * @author pekkis
 */
class GridfsStorageAdapter extends AbstractStorageAdapter
{
    /**
     * @var MongoDB Mongo reference
     */
    private $mongo;

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
    public function __construct(MongoDB $mongo, $prefix = 'xi_filelib', $tempDir = null)
    {
        $this->mongo = $mongo;
        $this->tempFiles = new TemporaryFileContainer($tempDir);
        $this->prefix = $prefix;
    }

    /**
     * Returns mongo
     *
     * @return MongoDB
     */
    protected function getMongo()
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


    public function versionExists(Storable $storable, $version)
    {
        $filename = $this->getFilenameVersion($storable, $version);
        $file = $this->getGridFS()->findOne(array('filename' => $filename));

        return (bool) $file;
    }


    /**
     * Writes a mongo file to temporary file and registers it as an internal temp file
     *
     * @param  MongoGridFSFile $resource
     * @return FileObject
     *
     */
    private function toTemp(MongoGridFSFile $file)
    {
        $tmp = $this->tempFiles->getTemporaryFilename();

        $file->write($tmp);
        $this->tempFiles->registerTemporaryFile($tmp);

        return $tmp;
    }

    public function store(Resource $resource, $tempFile)
    {
        $filename = $this->getFilename($resource);
        $this->getGridFS()->storeFile(
            $tempFile,
            array(
                'filename' => $filename,
                'metadata' => array(
                    'id' => $resource->getId(),
                    'version' => 'original'
                )
            )
        );
    }

    public function storeVersion(Storable $storable, $version, $tempFile)
    {
        $filename = $this->getFilenameVersion($storable, $version);
        $this->getGridFS()->storeFile(
            $tempFile,
            array(
                'filename' => $filename,
                'metadata' => array(
                    'id' => $storable->getId(),
                    'version' => $version
                )
            )
        );
    }

    public function retrieve(Resource $resource)
    {
        $filename = $this->getFilename($resource);
        $file = $this->getGridFS()->findOne(array('filename' => $filename));

        return $this->toTemp($file);
    }

    public function retrieveVersion(Storable $storable, $version)
    {
        $filename = $this->getFilenameVersion($storable, $version);
        $file = $this->getGridFS()->findOne(array('filename' => $filename));

        return $this->toTemp($file);
    }

    public function delete(Resource $resource)
    {
        $filename = $this->getFilename($resource);
        $this->getGridFS()->remove(array('filename' => $filename));
    }

    public function deleteVersion(Storable $storable, $version)
    {
        $filename = $this->getFilenameVersion($storable, $version);
        $this->getGridFS()->remove(array('filename' => $filename));
    }

    private function getFilename(Resource $resource)
    {
        return $resource->getId();
    }

    private function getFilenameVersion(Storable $storable, $version)
    {
        list($resource, $file) = $this->extractResourceAndFileFromStorable($storable);

        $path = $resource->getId() . '/' . $version;
        if ($file) {
            $path = '/' . $file->getId();
        }

        return $path;
    }
}
