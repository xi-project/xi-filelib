<?php

namespace Xi\Filelib\Tests\File;

use Xi\Filelib\Plugin\VersionProvider\Version;
use Xi\Filelib\Resource\Resource;
use DateTime;
use Xi\Filelib\Tests\BaseStorableTestCase;

class ResourceTest extends BaseStorableTestCase
{

    public function getClassName()
    {
        return 'Xi\Filelib\Resource\Resource';
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Resource\Resource'));
    }

    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $resource = Resource::create();

        $val = 666;
        $this->assertNull($resource->getId());
        $this->assertSame($resource, $resource->setId($val));
        $this->assertEquals($val, $resource->getId());

        $val = 'image/lus';
        $this->assertNull($resource->getMimetype());
        $this->assertSame($resource, $resource->setMimetype($val));
        $this->assertEquals($val, $resource->getMimetype());

        $val = 100000;
        $this->assertNull($resource->getSize());
        $this->assertSame($resource, $resource->setSize($val));
        $this->assertEquals($val, $resource->getSize());

        $val = new DateTime('1978-01-02');
        $this->assertInstanceOf('DateTime', $resource->getDateCreated());
        $this->assertSame($resource, $resource->setDateCreated($val));
        $this->assertSame($val, $resource->getDateCreated());

        $val = 'hasauttaja';
        $this->assertNull($resource->getHash());
        $this->assertSame($resource, $resource->setHash($val));
        $this->assertEquals($val, $resource->getHash());

        $val = true;
        $this->assertFalse($resource->isExclusive());
        $this->assertSame($resource, $resource->setExclusive($val));
        $this->assertTrue($val, $resource->getHash());

    }

    /**
     * @test
     */
    public function createInitializesValues()
    {
        $data = array(
            'id' => 1,
            'hash' => 'lussenhofer',
            'date_created' => new \DateTime('2010-01-01 01:01:01'),
            'data' => array(
                'versions' => array('tussenhof', 'luslus')
            ),
            'size' => 1234,
            'mimetype' => 'image/lus',
            'exclusive' => true,
        );

        $resource = Resource::create($data);

        $this->assertEquals($data['id'], $resource->getId());
        $this->assertEquals($data['hash'], $resource->getHash());
        $this->assertSame($data['date_created'], $resource->getDateCreated());
        $this->assertEquals($data['data'], $resource->getData()->toArray());
        $this->assertEquals($data['size'], $resource->getSize());
        $this->assertEquals($data['mimetype'], $resource->getMimetype());
        $this->assertEquals($data['exclusive'], $resource->isExclusive());
    }

    /**
     * @test
     */
    public function toArrayShouldWorkAsExpected()
    {
        $resource = Resource::create();
        $resource->setHash('hashisen-kone');
        $resource->setId(655);
        $resource->setDateCreated(new \DateTime('1978-03-21'));
        $resource->setMimetype('video/lus');
        $resource->setSize(5678);
        $resource->setExclusive(true);

        $resource->addVersion(Version::get('kraa'));
        $resource->addVersion(Version::get('xoo'));

        $this->assertEquals(array(
            'id' => 655,
            'hash' => 'hashisen-kone',
            'date_created' => new \DateTime('1978-03-21'),
            'data' => array('versions' => array('kraa', 'xoo')),
            'size' => 5678,
            'mimetype' => 'video/lus',
            'exclusive' => true,
        ), $resource->toArray());
    }

    /**
     * @test
     */
    public function createShouldCreateNewInstance()
    {
        $this->assertInstanceOf('Xi\Filelib\Resource\Resource', Resource::create(array()));
    }
}
