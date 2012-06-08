<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Storage;

use Xi\Filelib\Storage\AmazonS3Storage;
use Xi\Filelib\File\Resource;

/**
 * @group storage
 */
class AmazonS3StorageTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @var AmazonS3Storage
     */

    private $storage;

    private $file;

    /**
     * @var string
     */
    private $filePath;

    private $amazonService;

    public function setUp()
    {
        $this->filePath = realpath(ROOT_TESTS . '/data') . '/self-lussing-manatee.jpg';

        $this->amazonService = $this->getMockBuilder('Zend\Service\Amazon\S3\S3')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->amazonService->expects($this->any())
            ->method('isObjectAvailable')
            ->will($this->returnValue(true));

        $this->storage = new AmazonS3Storage(
            $this->amazonService,
            ROOT_TESTS . '/data/temp',
            'bucket'
        );

        $this->resource = $this->getMock('Xi\Filelib\File\Resource');
        $this->resource->expects($this->any())
                   ->method('getId')
                   ->will($this->returnValue(123));

        $this->file = $this->getMock('Xi\Filelib\File\File');
        $this->file->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(321));

    }

    /**
     * @test
     */
    public function storesFile()
    {
        $this->amazonService
             ->expects($this->once())
             ->method('putFile')
             ->with($this->filePath, 'bucket/123');

        $this->storage->store($this->resource, $this->filePath);
    }

    /**
     * @test
     */
    public function storesResourceVersion()
    {
        $this->amazonService
             ->expects($this->once())
             ->method('putFile')
             ->with($this->filePath, 'bucket/123_version');

        $this->storage->storeVersion($this->resource, 'version', $this->filePath);
    }

    /**
     * @test
     */
    public function storesFileVersion()
    {
        $this->amazonService
            ->expects($this->once())
            ->method('putFile')
            ->with($this->filePath, 'bucket/123_version_321');

        $this->storage->storeVersion($this->resource, 'version', $this->filePath, $this->file);
    }


    /**
     * @test
     */
    public function retrievesFile()
    {
        $this->amazonService
             ->expects($this->once())
             ->method('getObject')
             ->with('bucket/123')
             ->will($this->returnValue(file_get_contents($this->filePath)));

        $retrieved = $this->storage->retrieve($this->resource);

        $this->assertInstanceof('Xi\Filelib\File\FileObject', $retrieved);

        $this->assertFileEquals($this->filePath, $retrieved->getRealPath());
    }

    /**
     * @test
     */
    public function retrievesResourceVersion()
    {
        $this->amazonService
             ->expects($this->once())
             ->method('getObject')
             ->with('bucket/123_version')
             ->will($this->returnValue(file_get_contents($this->filePath)));

        $retrieved = $this->storage->retrieveVersion($this->resource, 'version');

        $this->assertInstanceof('Xi\Filelib\File\FileObject', $retrieved);

        $this->assertFileEquals($this->filePath, $retrieved->getRealPath());
    }

    /**
     * @test
     */
    public function retrievesFileVersion()
    {
        $this->amazonService
            ->expects($this->once())
            ->method('getObject')
            ->with('bucket/123_version_321')
            ->will($this->returnValue(file_get_contents($this->filePath)));

        $retrieved = $this->storage->retrieveVersion($this->resource, 'version', $this->file);

        $this->assertInstanceof('Xi\Filelib\File\FileObject', $retrieved);

        $this->assertFileEquals($this->filePath, $retrieved->getRealPath());
    }


    /**
     * @test
     */
    public function deletesFile()
    {
        $this->amazonService
             ->expects($this->once())
             ->method('removeObject')
             ->with('bucket/123');

        $this->storage->delete($this->resource);
    }

    /**
     * @test
     */
    public function deletesResourceVersion()
    {
        $this->amazonService
             ->expects($this->once())
             ->method('removeObject')
             ->with('bucket/123_version');

        $this->storage->deleteVersion($this->resource, 'version');
    }

    /**
     * @test
     */
    public function deletesFileVersion()
    {
        $this->amazonService
            ->expects($this->once())
            ->method('removeObject')
            ->with('bucket/123_version_321');

        $this->storage->deleteVersion($this->resource, 'version', $this->file);
    }


    /**
     * @test
     */
    public function destructorCleansUpTemporaryFiles()
    {
        $this->amazonService
             ->expects($this->once())
             ->method('getObject')
             ->with('bucket/123')
             ->will($this->returnValue(file_get_contents($this->filePath)));

        $retrievedPath = $this->storage->retrieve($this->resource)->getRealPath();

        $this->assertFileExists($retrievedPath);
        unset($this->storage);
        $this->assertFileNotExists($retrievedPath);
    }

    /**
     * @test
     */
    public function amazonServiceCreatesBucketIfItDoesNotExist()
    {
        $this->amazonService
             ->expects($this->once())
             ->method('isBucketAvailable')
             ->with('bucket')
             ->will($this->returnValue(false));

        $this->amazonService
             ->expects($this->once())
             ->method('createBucket')
             ->with('bucket');

        $this->storage->store($this->resource, $this->filePath);
    }
}
