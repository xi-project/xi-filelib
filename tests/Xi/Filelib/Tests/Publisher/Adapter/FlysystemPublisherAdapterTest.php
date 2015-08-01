<?php

namespace Xi\Filelib\Tests\Publisher\Adapter;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Xi\Filelib\File\File;
use Xi\Filelib\Publisher\Adapter\FlysystemPublisherAdapter;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Publisher\Adapter\AmazonS3PublisherAdapter;
use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Tests\TestCase;
use Aws\S3\S3Client;

class FlysystemPublisherAdapterTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $linker;

    /**
     * @var FlysystemPublisherAdapter
     */
    private $adapter;

    /**
     * @var Filesystem
     */
    private $filesystem;

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
        $this->version = Version::get('xooxer');

        $path = ROOT_TESTS . '/data/publisher/public';

        $this->filesystem = new Filesystem(new Local($path));

        $this->adapter = new FlysystemPublisherAdapter(
            $this->filesystem,
            '/files'
        );

        $this->storage = $this->getMockedStorage();

        $filelib = $this->getMockedFilelib(null, array(
            'storage' => $this->storage
        ));

        $this->linker = $this->getMockedLinker();

        $this->adapter->attachTo($filelib);

        $this->vp = $this->getMockedVersionProvider(array('xooxer'), false);

        $this->resource = ConcreteResource::create();
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
        $deletor = new RecursiveDirectoryDeletor('publisher/public');
        $deletor->delete();
    }

    /**
     * @test
     */
    public function publishesAndUnpublishes()
    {
        $this->assertFalse($this->filesystem->has($this->path));

        $this->assertTrue($this->adapter->publish($this->file, Version::get('xooxer'), $this->vp, $this->linker));
        $this->assertTrue($this->filesystem->has($this->path));
        $this->assertFalse($this->adapter->publish($this->file, Version::get('xooxer'), $this->vp, $this->linker));

        $this->assertTrue($this->adapter->unpublish($this->file, Version::get('xooxer'), $this->vp, $this->linker));
        $this->assertFalse($this->filesystem->has($this->path));
        $this->assertFalse($this->adapter->unpublish($this->file, Version::get('xooxer'), $this->vp, $this->linker));

    }

    /**
     * @test
     */
    public function getsUrl()
    {
        $url = $this->adapter->getUrl($this->file, $this->version, $this->vp, $this->linker);

        $this->assertEquals(
            '/files/' . $this->path,
            $url
        );
    }
}
