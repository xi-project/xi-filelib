<?php

namespace Xi\Filelib\Tests\Publisher\Adapter;

use Xi\Filelib\File\File;
use Xi\Filelib\Version;
use Xi\Filelib\Publisher\Adapter\AmazonS3PublisherAdapter;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Tests\TestCase;
use Aws\S3\S3Client;

class AmazonS3PublisherTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $linker;

    /**
     * @var AmazonS3PublisherAdapter
     */
    private $adapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $vp;

    private $file;

    private $resource;

    private $path;

    private $version;

    public function setUp()
    {
        if (!getenv('S3_KEY')) {
            $this->markTestSkipped('S3 not configured');
            return;
        }

        $this->version = Version::get('xooxer');

        $this->adapter = new AmazonS3PublisherAdapter(getenv('S3_KEY'), getenv('S3_SECRETKEY'), getenv('S3_BUCKET'));

        $this->storage = $this->getMockedStorage();

        $filelib = $this->getMockedFilelib(null, array(
            'storage' => $this->storage
        ));

        $this->linker = $this->getMockedLinker();

        $this->adapter->attachTo($filelib);

        $this->vp = $this->getMockedVersionProvider(array('xooxer'), false);

        $this->resource = Resource::create();
        $this->file = File::create(array('resource' => $this->resource));

        $this->vp
            ->expects($this->any())
            ->method('getMimeType')
            ->with($this->file, $this->version)
            ->will($this->returnValue('image/jpeg'));

        $this->vp
            ->expects($this->any())
            ->method('getExtension')
            ->with($this->file, $this->version)
            ->will($this->returnValue('jpg'));

        $this->vp
            ->expects($this->any())
            ->method('getApplicableVersionable')
            ->with($this->file)
            ->will($this->returnValue($this->resource));

        $this->storage
            ->expects($this->any())
            ->method('retrieveVersion')
            ->with($this->resource, $this->version)
            ->will($this->returnValue(ROOT_TESTS . '/data/self-lussing-manatee.jpg'));

        $this->path = 'lusso/grande/ankan-lipaisija.jpg';

        $this->linker
            ->expects($this->any())
            ->method('getLink')
            ->with($this->file, $this->version, 'jpg')
            ->will($this->returnValue($this->path));
    }

    public function tearDown()
    {
        if (!getenv('S3_KEY')) {
            $this->markTestSkipped('S3 not configured');
            return;
        }

        $client = $this->adapter->getClient();
        $client->deleteObject(
            array(
                'Bucket' => getenv('S3_BUCKET'),
                'Key' => $this->path,
            )
        );
    }


    /**
     * @test
     */
    public function createsClient()
    {
        $this->assertInstanceOf('Aws\S3\S3Client', $this->adapter->getClient());
    }

    /**
     * @test
     */
    public function publishesAndUnpublishes()
    {
        $client = $this->adapter->getClient();
        $this->assertFalse($client->doesObjectExist(getenv('S3_BUCKET'), $this->path));

        $this->adapter->publish($this->file, $this->version, $this->vp, $this->linker);
        $this->assertTrue($client->doesObjectExist(getenv('S3_BUCKET'), $this->path));

        $this->adapter->unpublish($this->file, $this->version, $this->vp, $this->linker);
        $this->assertFalse($client->doesObjectExist(getenv('S3_BUCKET'), $this->path));
    }

    /**
     * @test
     */
    public function getsUrl()
    {
        $url = $this->adapter->getUrl($this->file, $this->version, $this->vp, $this->linker);

        $this->assertEquals(
            'https://' . getenv('S3_BUCKET') . '.s3.amazonaws.com/' . $this->path,
            $url
        );
    }
}
