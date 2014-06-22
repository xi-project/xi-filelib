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
use Xi\Filelib\Storage\Retrieved;
use Xi\Filelib\Storage\Versionable;
use Xi\Filelib\Version;

/**
 * Stores files in MongoDB's GridFS filesystem
 *
 * @author pekkis
 */
class GridfsStorageAdapter extends BaseTemporaryRetrievingStorageAdapter
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
     * @param MongoDB $mongo
     * @param string $prefix
     * @param null $tempDir
     */
    public function __construct(MongoDB $mongo, $prefix = 'xi_filelib')
    {
        $this->mongo = $mongo;
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


    public function versionExists(Versionable $versionable, Version $version)
    {
        $filename = $this->getFilenameVersion($versionable, $version);
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
        $tmp = $this->getTemporaryFilename();
        $file->write($tmp);
        return new Retrieved($tmp, true);
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

    public function storeVersion(Versionable $versionable, Version $version, $tempFile)
    {
        $filename = $this->getFilenameVersion($versionable, $version);
        $this->getGridFS()->storeFile(
            $tempFile,
            array(
                'filename' => $filename,
                'metadata' => array(
                    'id' => $versionable->getId(),
                    'version' => $version->toString()
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

    public function retrieveVersion(Versionable $versionable, Version $version)
    {
        $filename = $this->getFilenameVersion($versionable, $version);
        $file = $this->getGridFS()->findOne(array('filename' => $filename));

        return $this->toTemp($file);
    }

    public function delete(Resource $resource)
    {
        $filename = $this->getFilename($resource);
        $this->getGridFS()->remove(array('filename' => $filename));
    }

    public function deleteVersion(Versionable $versionable, Version $version)
    {
        $filename = $this->getFilenameVersion($versionable, $version);
        $this->getGridFS()->remove(array('filename' => $filename));
    }

    private function getFilename(Resource $resource)
    {
        return $resource->getId();
    }

    private function getFilenameVersion(Versionable $versionable, Version $version)
    {
        list($resource, $file) = $this->extractResourceAndFileFromVersionable($versionable);

        $path = $resource->getId() . '/' . $version->toString();
        if ($file) {
            $path = '/' . $file->getId();
        }

        return $path;
    }
}
