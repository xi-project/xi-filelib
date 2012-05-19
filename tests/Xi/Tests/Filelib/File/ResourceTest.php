<?php

namespace Xi\Tests\Filelib\File;

use Xi\Filelib\File\Resource;
use DateTime;

class ResourceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Resource'));
    }


    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $resource = new Resource();

        $val = 666;
        $this->assertNull($resource->getId());
        $this->assertSame($resource, $resource->setId($val));
        $this->assertEquals($val, $resource->getId());

        $val = new DateTime('1978-01-02');
        $this->assertNull($resource->getDateCreated());
        $this->assertSame($resource, $resource->setDateCreated($val));
        $this->assertSame($val, $resource->getDateCreated());

        $val = 'hasauttaja';
        $this->assertNull($resource->getHash());
        $this->assertSame($resource, $resource->setHash($val));
        $this->assertEquals($val, $resource->getHash());

    }


    public function fromArrayProvider()
    {
        return array(
            array(
                array(
                    'id' => 1,
                    'hash' => 'lussenhofer',
                    'date_created' => new \DateTime('2010-01-01 01:01:01'),
                ),
            ),
            array(
                array(
                    'hash' => 'lussenhoff',
                ),
            ),

        );
    }

    /**
     * @dataProvider fromArrayProvider
     * @test
     */
    public function fromArrayShouldWorkAsExpected($data)
    {
        $resource = new Resource();
        $resource->fromArray($data);

        $map = array(
            'id' => 'getId',
            'hash' => 'getHash',
            'date_created' => 'getDateCreated',
        );

        foreach($map as $key => $method) {
            if(isset($data[$key])) {
                $this->assertEquals($data[$key], $resource->$method());
            } else {
                $this->assertNull($resource->$method());
            }
        }

    }

    /**
     * @test
     */
    public function toArrayShouldWorkAsExpected()
    {
        $resource = new Resource();
        $resource->setHash('hashisen-kone');
        $resource->setId(655);
        $resource->setDateCreated(new \DateTime('1978-03-21'));

        $this->assertEquals($resource->toArray(), array(
            'id' => 655,
            'hash' => 'hashisen-kone',
            'date_created' => new \DateTime('1978-03-21'),
        ));


        $resource = new Resource();
        $this->assertEquals($resource->toArray(), array(
            'id' => null,
            'hash' => null,
            'date_created' => null,
        ));
    }

    /**
     * @test
     */
    public function createShouldCreateNewInstance()
    {
        $this->assertInstanceOf('Xi\Filelib\File\Resource', Resource::create(array()));
    }


}