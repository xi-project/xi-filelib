<?php

namespace Xi\Tests\Filelib\Backend\Platform\DoctrineOrm\Entity;

use Xi\Filelib\Backend\Platform\DoctrineOrm\Entity\Resource;
use DateTime;

class ResourceTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Backend\Platform\DoctrineOrm\Entity\Resource'));
    }


    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $resource = new Resource();

        $this->assertNull($resource->getId());

        $value = 'mime/type';
        $this->assertNull($resource->getMimeType());
        $this->assertSame($resource, $resource->setMimeType($value));
        $this->assertEquals($value, $resource->getMimeType());

        $value = 500000;
        $this->assertNull($resource->getSize());
        $this->assertSame($resource, $resource->setSize($value));
        $this->assertEquals($value, $resource->getSize());

        $value = 'xooxer';
        $this->assertNull($resource->getHash());
        $this->assertSame($resource, $resource->setHash($value));
        $this->assertEquals($value, $resource->getHash());

        $value = array('xoo', 'xer');
        $this->assertEquals(array(), $resource->getVersions());
        $this->assertSame($resource, $resource->setVersions($value));
        $this->assertEquals($value, $resource->getVersions());

        $value = new DateTime('2011-04-05');
        $this->assertNull($resource->getDateCreated());
        $this->assertSame($resource, $resource->setDateCreated($value));
        $this->assertSame($value, $resource->getDateCreated());

    }
}
