<?php

use \Xi\Filelib\Storage\AmazonS3Storage,
    \Xi\Filelib\File\File
    ;

/**
 * @group storage
 */
class AmazonS3StorageTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     *
     * @var AmazonS3Storage
     */
    protected $storage;

    protected $file;

    protected $versionProvider;

    protected $fileResource;

    protected $filelib;


    public function setUp()
    {

        if (!class_exists('Zend_Service_Amazon_S3')) {
            $this->markTestSkipped('Zend_Service_Amazon_S3 class could not be loaded');
        }

        if (!S3_KEY) {
            $this->markTestSkipped('S3 not configured');
        }


        $this->fileResource = realpath(ROOT_TESTS . '/data') . '/self-lussing-manatee.jpg';

        $this->filelib = $this->getFilelib();

        $storage = new AmazonS3Storage();
        $storage->setKey(S3_KEY);
        $storage->setSecretKey(S3_SECRETKEY);
        $storage->setBucket(S3_BUCKET);


        $this->storage = $storage;

        $dc = $this->getMock('\Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->any())
            ->method('calculateDirectoryId')
            ->will($this->returnValue('1'));

        $this->version = 'xoo';

        $this->file = \Xi\Filelib\File\File::create(array('id' => 1, 'folder_id' => 1, 'name' => 'self-lussing-manatee.jpg'));





    }


    public function tearDown()
    {
        if (!class_exists('Zend_Service_Amazon_S3')) {
            return;
        }

        if (!S3_KEY) {
            $this->markTestSkipped('S3 not configured');
        }

        $this->storage->getAmazonService()->cleanBucket($this->storage->getBucket());

    }


    /**
     * @test
     */
    public function storeAndRetrieveAndDeleteShouldWorkSeamlessly()
    {
        $this->storage->setFilelib($this->getFilelib());
        $this->storage->store($this->file, $this->fileResource);

        $retrieved = $this->storage->retrieve($this->file);

        $this->assertInstanceof('\Xi\Filelib\File\FileObject', $retrieved);

        $this->assertFileEquals($this->fileResource, $retrieved->getRealPath());

        $this->storage->delete($this->file);

        $ret = $this->storage->getAmazonService()->isObjectAvailable($this->storage->getPath($this->file));

        $this->assertFalse($ret);

        $this->assertFileEquals($this->fileResource, $retrieved->getRealPath());

    }

    /**
     * @test
     */
    public function destructorShouldCleanUpTheStoragesMess()
    {
        $storage = new AmazonS3Storage();
        $storage->setFilelib($this->getFilelib());
        $storage->setKey(S3_KEY);
        $storage->setSecretKey(S3_SECRETKEY);
        $storage->setBucket(S3_BUCKET);

        $storage->store($this->file, $this->fileResource);

        $retrieved = $storage->retrieve($this->file);
        $this->assertInstanceof('\Xi\Filelib\File\FileObject', $retrieved);

        $retrievedPath = $retrieved->getRealPath();

        unset($storage);

        $this->assertFileNotExists($retrievedPath);



    }

    /**
     * @test
     */
    public function storeAndRetrieveAndDeleteVersionShouldWorkSeamlessly()
    {
        $this->storage->setFilelib($this->getFilelib());
        $this->storage->storeVersion($this->file, $this->version, $this->fileResource);

        $retrieved = $this->storage->retrieveVersion($this->file, $this->version);
        $this->assertInstanceof('\Xi\Filelib\File\FileObject', $retrieved);

        $this->storage->deleteVersion($this->file, $this->version);

        $ret = $this->storage->getAmazonService()->isObjectAvailable($this->storage->getPath($this->file) . '_' . $this->version);

        $this->assertFalse($ret);

    }

    /**
     * @test
     */
    public function amazonServiceShouldCreateBucketIfItDoesNotExist()
    {
        $storage = new AmazonS3Storage();
        $storage->setFilelib($this->getFilelib());
        $storage->setKey(S3_KEY);
        $storage->setSecretKey(S3_SECRETKEY);
        $storage->setBucket(S3_BUCKET . '_lus');

        $storage->store($this->file, $this->fileResource);

        $retrieved = $storage->retrieve($this->file);
        $this->assertInstanceof('Xi\Filelib\File\FileObject', $retrieved);

        $storage->getAmazonService()->cleanBucket($storage->getBucket());
        $storage->getAmazonService()->removeBucket($storage->getBucket());
    }



}


