<?php

namespace Xi\Filelib\Tests\Publisher\Adapter;

use Xi\Filelib\File\File;
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

    public function setUp()
    {
        if (!S3_KEY) {
            $this->markTestSkipped('S3 not configured');
            return;
        }
        $this->adapter = new AmazonS3PublisherAdapter(S3_KEY, S3_SECRETKEY, S3_BUCKET);

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
            ->method('getMimetype')
            ->with($this->file, 'xooxer')
            ->will($this->returnValue('image/jpeg'));

        $this->vp
            ->expects($this->any())
            ->method('getExtension')
            ->with($this->file, 'xooxer')
            ->will($this->returnValue('jpg'));

        $this->vp
            ->expects($this->any())
            ->method('getApplicableStorable')
            ->with($this->file)
            ->will($this->returnValue($this->resource));

        $this->storage
            ->expects($this->any())
            ->method('retrieveVersion')
            ->with($this->resource, 'xooxer')
            ->will($this->returnValue(ROOT_TESTS . '/data/self-lussing-manatee.jpg'));

        $this->path = 'lusso/grande/ankan-lipaisija.jpg';

        $this->linker
            ->expects($this->any())
            ->method('getLink')
            ->with($this->file, 'xooxer', 'jpg')
            ->will($this->returnValue($this->path));

    }

    public function tearDown()
    {
        $client = $this->adapter->getClient();
        $client->deleteObject(
            array(
                'Bucket' => S3_BUCKET,
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
        $this->assertFalse($client->doesObjectExist(S3_BUCKET, $this->path));

        $this->adapter->publish($this->file, 'xooxer', $this->vp, $this->linker);
        $this->assertTrue($client->doesObjectExist(S3_BUCKET, $this->path));

        $this->adapter->unpublish($this->file, 'xooxer', $this->vp, $this->linker);
        $this->assertFalse($client->doesObjectExist(S3_BUCKET, $this->path));
    }

    /**
     * @test
     */
    public function getsUrl()
    {
        $url = $this->adapter->getUrl($this->file, 'xooxer', $this->vp, $this->linker);

        $this->assertEquals(
            'https://' . S3_BUCKET . '.s3.amazonaws.com/' . $this->path,
            $url
        );
    }
}
