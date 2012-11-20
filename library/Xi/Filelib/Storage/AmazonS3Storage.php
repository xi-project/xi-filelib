<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Storage\AbstractStorage;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use ZendService\Amazon\S3\S3 as AmazonService;

class AmazonS3Storage extends AbstractStorage implements Storage
{
    /**
     * @var AmazonService
     */
    private $amazonService;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var string
     */
    private $bucket;

    /**
     * @var array Registered temporary files
     */
    private $tempFiles = array();

    /**
     * @param  AmazonService   $amazonService
     * @param  string          $tempDir
     * @param  string          $bucket
     * @return AmazonS3Storage
     */
    public function __construct(AmazonService $amazonService, $tempDir, $bucket)
    {
        $this->amazonService = $amazonService;
        $this->tempDir = $tempDir;
        $this->bucket = $bucket;
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
     * @return string
     */
    public function getBucket()
    {
        return $this->bucket;
    }

    /**
     * Stores an uploaded file
     *
     * @param File   $file
     * @param string $tempFile File to be stored
     */
    public function store(File $file, $tempFile)
    {
        $object = $this->getPath($file);
        $this->getAmazonService()->putFile($tempFile, $object);
    }

    /**
     * Stores a version of a file
     *
     * @param File   $file
     * @param string $version
     * @param string $tempFile File to be stored
     */
    public function storeVersion(File $file, $version, $tempFile)
    {
        $object = $this->getPath($file) . '_' . $version;
        $this->getAmazonService()->putFile($tempFile, $object);
    }

    /**
     * Retrieves a file and temporarily stores it somewhere so it can be read.
     *
     * @param  File       $file
     * @return FileObject
     */
    public function retrieve(File $file)
    {
        $object = $this->getPath($file);
        $ret = $this->getAmazonService()->getObject($object);

        return $this->toTemp($ret);
    }

    /**
     * Retrieves a version of a file and temporarily stores it somewhere so it
     * can be read.
     *
     * @param  File       $file
     * @param  string     $version
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
     * @param File   $file
     * @param string $version
     */
    public function deleteVersion(File $file, $version)
    {
        $object = $this->getPath($file) . '_' . $version;
        $this->getAmazonService()->removeObject($object);
    }

    /**
     * @param  string     $file
     * @return FileObject
     */
    private function toTemp($file)
    {
        $tmp = tempnam($this->tempDir, 'filelib');

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

    /**
     * @param  File   $file
     * @return string
     */
    private function getPath(File $file)
    {
        return $this->getBucket() . '/' . $file->getId();
    }

    /**
     * @return AmazonService
     */
    private function getAmazonService()
    {
        if (!$this->amazonService->isBucketAvailable($this->getBucket())) {
            $this->amazonService->createBucket($this->getBucket());
        }

        return $this->amazonService;
    }
}
