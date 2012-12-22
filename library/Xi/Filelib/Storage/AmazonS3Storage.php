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
use Xi\Filelib\File\Resource;
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
    public function __construct(AmazonService $amazonService, $tempDir, $bucket, $options = array())
    {
        $this->amazonService = $amazonService;
        $this->tempDir = $tempDir;
        $this->bucket = $bucket;
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
     * @return string
     */

    public function getBucket()
    {
        return $this->bucket;
    }


    public function exists(Resource $resource)
    {
        return $this->getAmazonService()->isObjectAvailable($this->getPath($resource));
    }

    public function versionExists(Resource $resource, $version, File $file = null)
    {
        return $this->getAmazonService()->isObjectAvailable($this->getPathVersion($resource, $version, $file));
    }


    protected function doStore(Resource $resource, $tempFile)
    {
        $object = $this->getPath($resource);
        $this->getAmazonService()->putFile($tempFile, $object);
    }

    protected function doStoreVersion(Resource $resource, $version, $tempFile, File $file = null)
    {
        $path = $this->getPathVersion($resource, $version, $file);
        $this->getAmazonService()->putFile($tempFile, $path);
    }

    protected function doRetrieve(Resource $resource)
    {
        $path = $this->getPath($resource);
        $ret = $this->getAmazonService()->getObject($path);
        return $this->toTemp($ret);
    }

    protected function doRetrieveVersion(Resource $resource, $version, File $file = null)
    {
        $path = $this->getPathVersion($resource, $version, $file);
        $ret = $this->getAmazonService()->getObject($path);
        return $this->toTemp($ret);
    }

    protected function doDelete(Resource $resource)
    {
        $path = $this->getPath($resource);
        $this->getAmazonService()->removeObject($path);
    }

    protected function doDeleteVersion(Resource $resource, $version, File $file = null)
    {
        $path = $this->getPathVersion($resource, $version, $file);
        $this->getAmazonService()->removeObject($path);
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
     * @param  Resource $resource
     * @return string
     */
    private function getPath(Resource $resource)
    {
        return $this->getBucket() . '/' . $resource->getId();
    }

    private function getPathVersion(Resource $resource, $version, File $file = null)
    {
        $path = $this->getPath($resource) . '_' . $version;
        if ($file) {
            $path .= '_' . $file->getId();
        }
        return $path;
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
