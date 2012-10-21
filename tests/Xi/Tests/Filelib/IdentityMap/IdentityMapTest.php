<?php

namespace Xi\Tests\Filelib;

use Xi\Tests\Filelib\TestCase;
use Xi\Filelib\IdentityMap\IdentityMap;
use Xi\Filelib\IdentityMap\Identifiable;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;

class IdentityMapTest extends TestCase
{

    /**
     * @var IdentityMap
     */
    protected $im;

    public function setUp()
    {
        $this->im = new IdentityMap();
    }

    /**
     * @return array
     */
    public function provideAddableObjects()
    {
        return array(
            array(File::create(array('id' => 1))),
            array(Resource::create(array('id' => 'xooxo'))),
            array(Folder::create(array('id' => 665))),
        );
    }

    /**
     * @test
     * @dataProvider provideAddableObjects
     */
    public function hasShouldReturnFalseForUnidentifiedObject(Identifiable $object)
    {
        $this->assertFalse($this->im->has($object));
    }

    /**
     * @test
     * @dataProvider provideAddableObjects
     */
    public function hasShouldReturnFalseForUnidentifiedObjectButTrueAfterItHasBeenAdded(Identifiable $object)
    {
        $this->assertFalse($this->im->has($object));
        $this->im->add($object);
        $this->assertTrue($this->im->has($object));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\IdentityMap\IdentityMapException
     */
    public function addShouldThrowExceptionWhenAddingObjectWithoutId()
    {
        $this->im->add(File::create());
    }

    /**
     * @test
     * @dataProvider provideAddableObjects
     */
    public function addingAnAlreadyExistingObjectShouldReturnFalse(Identifiable $object)
    {
        $this->assertFalse($this->im->has($object));
        $ret = $this->im->add($object);

        $this->assertTrue($ret);
        $this->assertTrue($this->im->has($object));

        $ret = $this->im->add($object);

        $this->assertFalse($ret);
        $this->assertTrue($this->im->has($object));
    }

    /**
     * @test
     */
    public function getShouldReturnFalseWhenObjectNotFound()
    {
        $ret = $this->im->get(1, 'Xi\Filelib\File\File');
        $this->assertFalse($ret);
    }

    /**
     * @test
     * @dataProvider provideAddableObjects
     */
    public function getShouldReturnSameInstanceWhenObjectIsFound(Identifiable $object)
    {
        $this->im->add($object);
        $this->assertSame($object, $this->im->get($object->getId(), get_class($object)));
    }

    /**
     * @test
     * @dataProvider provideAddableObjects
     */
    public function removeShouldRemoveObject(Identifiable $object)
    {
        $this->im->add($object);
        $this->assertTrue($this->im->has($object));

        $ret = $this->im->remove($object);
        $this->assertTrue($ret);

        $this->assertFalse($this->im->has($object));
        $this->assertFalse($this->im->get($object->getId(), get_class($object)));
    }

    /**
     * @test
     */
    public function removeShouldCleanInternalStateCorrectly()
    {
        $this->assertAttributeCount(0, 'objects', $this->im);
        $this->assertAttributeCount(0, 'objectIdentifiers', $this->im);

        $files = array(
            File::create(array('id' => 1)),
            File::create(array('id' => 2)),
            File::create(array('id' => 3)),
        );

        foreach ($files as $file) {
            $this->im->add($file);
        }

        $this->assertAttributeCount(3, 'objects', $this->im);
        $this->assertAttributeCount(3, 'objectIdentifiers', $this->im);

        foreach ($files as $file) {
            $this->im->remove($file);
        }

        $this->assertAttributeCount(0, 'objects', $this->im);
        $this->assertAttributeCount(0, 'objectIdentifiers', $this->im);

    }


    /**
     * @test
     * @dataProvider provideAddableObjects
     */
    public function removingUnexistingObjectShouldReturnFalse(Identifiable $object)
    {
        $this->assertFalse($this->im->has($object));
        $ret = $this->im->remove($object);
        $this->assertFalse($ret);

    }


}
