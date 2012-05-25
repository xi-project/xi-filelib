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

        $val = array('lussen', 'le', 'tussen');
        $this->assertEquals(array(), $resource->getVersions());
        $this->assertSame($resource, $resource->setVersions($val));
        $this->assertEquals($val, $resource->getVersions());


    }


    public function fromArrayProvider()
    {
        return array(
            array(
                array(
                    'id' => 1,
                    'hash' => 'lussenhofer',
                    'date_created' => new \DateTime('2010-01-01 01:01:01'),
                    'versions' => array('tussenhof', 'luslus'),
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
            'versions' => 'getVersions',
        );

        foreach($map as $key => $method) {
            if(isset($data[$key])) {
                $this->assertEquals($data[$key], $resource->$method());
            } else {

                if ($key !== 'versions') {
                    $this->assertNull($resource->$method());
                } else {
                    $this->assertEquals(array(), $resource->$method());
                }
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
        $resource->setVersions(array('kraa', 'xoo'));

        $this->assertEquals($resource->toArray(), array(
            'id' => 655,
            'hash' => 'hashisen-kone',
            'date_created' => new \DateTime('1978-03-21'),
            'versions' => array('kraa', 'xoo'),
        ));


        $resource = new Resource();
        $this->assertEquals($resource->toArray(), array(
            'id' => null,
            'hash' => null,
            'date_created' => null,
            'versions' => array(),
        ));
    }

    /**
     * @test
     */
    public function createShouldCreateNewInstance()
    {
        $this->assertInstanceOf('Xi\Filelib\File\Resource', Resource::create(array()));
    }


    /**
     * @test
     */
    public function addVersionShouldAddVersion()
    {
        $resource = Resource::create(array('versions' => array('tussi', 'watussi')));
        $resource->addVersion('lussi');

        $this->assertEquals(array('tussi', 'watussi', 'lussi'), $resource->getVersions());
    }


    /**
     * @test
     */
    public function addVersionShouldNotAddVersionIfVersionExists()
    {
        $resource = Resource::create(array('versions' => array('tussi', 'watussi')));
        $resource->addVersion('watussi');

        $this->assertEquals(array('tussi', 'watussi'), $resource->getVersions());
    }


    /**
     * @test
     */
    public function removeVersionShouldRemoveVersion()
    {
        $resource = Resource::create(array('versions' => array('tussi', 'watussi')));
        $resource->removeVersion('watussi');

        $this->assertEquals(array('tussi'), $resource->getVersions());
    }


    /**
     * @test
     */
    public function hasResourceShouldReturnWhetherResourceHasVersion()
    {
        $resource = Resource::create(array('versions' => array('tussi', 'watussi')));

        $this->assertTrue($resource->hasVersion('tussi'));
        $this->assertTrue($resource->hasVersion('watussi'));
        $this->assertFalse($resource->hasVersion('lussi'));
    }


}