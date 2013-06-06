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
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\FilelibException;

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
    public function __construct(MongoDB $mongo, $tempDir)
    {
        $this->setMongo($mongo);

        $this->tempDir = $tempDir;
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

    /**
     * Writes a mongo file to temporary file and registers it as an internal temp file
     *
     * @param  MongoGridFSFile $file
     * @return FileObject
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

    /**
     *
     * @param File $file
     * @param string $tempFile
     */
    public function store(File $file, $tempFile)
    {
        $filename = $this->getFilename($file);

        $this->getGridFS()->storeFile($tempFile, array('filename' => $filename, 'metadata' => array('id' => $file->getId(), 'version' => 'original', 'mimetype' => $file->getMimetype()) ));
    }

    /**
     *
     * @param File $file
     * @param string $version
     * @param string $tempFile
     */
    public function storeVersion(File $file, $version, $tempFile)
    {
        $filename = $this->getFilenameVersion($file, $version);

        $this->getGridFS()->storeFile($tempFile, array('filename' => $filename, 'metadata' => array('id' => $file->getId(), 'version' => $version, 'mimetype' => $file->getMimetype()) ));
    }

    /**
     *
     * @param File $file
     * @return FileObject
     * @throws FilelibException
     */
    public function retrieve(File $file)
    {
        $filename = $this->getFilename($file);

        $file = $this->getGridFS()->findOne(array('filename' => $filename));

        if (!$file) {
            throw new FilelibException("Filename '{$filename}' not retrievable");
        }

        return $this->toTemp($file);
    }

    /**
     *
     * @param File $file
     * @param string $version
     * @return FileObject
     * @throws FilelibException
     */
    public function retrieveVersion(File $file, $version)
    {
        $filename = $this->getFilenameVersion($file, $version);

        $file = $this->getGridFS()->findOne(array('filename' => $filename));

        if (!$file) {
            throw new FilelibException("Filename '{$filename}' not retrievable");
        }

        return $this->toTemp($file);
    }

    /**
     *
     * @param File $file
     */
    public function delete(File $file)
    {
        $filename = $this->getFilename($file);

        $this->getGridFS()->remove(array('filename' => $filename));
    }

    /**
     *
     * @param File $file
     * @param string $version
     */
    public function deleteVersion(File $file, $version)
    {
        $filename = $this->getFilenameVersion($file, $version);

        $this->getGridFS()->remove(array('filename' => $filename));
    }

    /**
     *
     * @param File $file
     * @return string
     */
    public function getFilename(File $file)
    {
        return $file->getFolderId() . '/' . $file->getId();
    }

    /**
     *
     * @param \Xi\Filelib\File\File $file
     * @param string $version
     * @return string
     */
    public function getFilenameVersion(File $file, $version)
    {
        return $file->getFolderId() . '/' . $file->getId() . '/' . $version;
    }
}
