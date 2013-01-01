<?php

namespace Xi\Tests\Filelib;

use Xi\Tests\Filelib\TestCase;
use Xi\Filelib\IdentityMap\IdentityMap;
use Xi\Filelib\IdentityMap\Identifiable;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\FolderEvent;
use ArrayIterator;

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
     */
    public function implementsEventSubscriberInterface()
    {
        $this->assertContains(
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            class_implements('Xi\Filelib\IdentityMap\IdentityMap')
        );
    }

    /**
     * @test
     */
    public function subscribesToCorrectEvents()
    {
        $this->assertEquals(
            array('file.upload', 'file.delete', 'folder.delete', 'folder.create'),
            array_keys(IdentityMap::getSubscribedEvents())
        );
    }

    /**
     * @test
     */
    public function onCreateAddsFile()
    {
        $im = $this->getMockBuilder('Xi\Filelib\IdentityMap\IdentityMap')
            ->setMethods(array('add', 'remove'))
            ->getMock();

        $file = File::create();
        $event = new FileEvent($file);

        $im
            ->expects($this->once())
            ->method('add')
            ->with($file);

        $im->onCreate($event);
    }

    /**
     * @test
     */
    public function onDeleteRemovesFolder()
    {
        $im = $this->getMockBuilder('Xi\Filelib\IdentityMap\IdentityMap')
            ->setMethods(array('add', 'remove'))
            ->getMock();

        $folder = Folder::create();
        $event = new FolderEvent($folder);

        $im
            ->expects($this->once())
            ->method('remove')
            ->with($folder);

        $im->onDelete($event);
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
    public function addManyShouldAddAllObjects()
    {
        $array = array(
            File::create(array('id' => 6)),
            Folder::create(array('id' => 6)),
            File::create(array('id' => 6)),
        );
        $iter = new ArrayIterator($array);

        $im = $this->getMockBuilder('Xi\Filelib\IdentityMap\IdentityMap')
                   ->setMethods(array('add'))
                   ->getMock();

        $im->expects($this->exactly(3))
            ->method('add')
            ->with($this->isInstanceOf('Xi\Filelib\IdentityMap\Identifiable'));

        $im->addMany($iter);
    }

    /**
     * @test
     */
    public function removeManyShouldDeleteAllObjects()
    {
        $array = array(
            File::create(array('id' => 6)),
            Folder::create(array('id' => 6)),
            File::create(array('id' => 6)),
        );
        $iter = new ArrayIterator($array);

        $im = $this->getMockBuilder('Xi\Filelib\IdentityMap\IdentityMap')
            ->setMethods(array('remove'))
            ->getMock();

        $im->expects($this->exactly(3))
            ->method('remove')
            ->with($this->isInstanceOf('Xi\Filelib\IdentityMap\Identifiable'));

        $im->removeMany($iter);
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
