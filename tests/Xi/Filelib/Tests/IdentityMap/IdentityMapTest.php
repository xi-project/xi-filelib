<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\IdentityMap\IdentityMap;
use Xi\Filelib\IdentityMap\Identifiable;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\FolderEvent;
use ArrayIterator;
use Xi\Filelib\Events;

class IdentityMapTest extends TestCase
{
    /**
     * @var IdentityMap
     */
    protected $im;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ed;

    public function setUp()
    {
        $this->ed = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->im = new IdentityMap($this->ed);
    }

    /**
     * @test
     */
    public function constructorSubscribesToEvents()
    {
        $this->ed
            ->expects($this->once())->method('addSubscriber')
            ->with(
                $this->isInstanceOf('Xi\Filelib\IdentityMap\IdentityMap')
            );

        $im = new IdentityMap($this->ed);
    }

    /**
     * @test
     */
    public function getEventDispatcherReturnsEventDispatcher()
    {
        $this->assertSame($this->ed, $this->im->getEventDispatcher());
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
            array(
                Events::FILE_AFTER_CREATE,
                Events::FILE_AFTER_DELETE,
                Events::FOLDER_AFTER_DELETE,
                Events::FOLDER_AFTER_CREATE,
                Events::RESOURCE_AFTER_CREATE,
                Events::RESOURCE_AFTER_DELETE,
            ),
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
            ->disableOriginalConstructor()
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
            ->disableOriginalConstructor()
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
        $this->ed
            ->expects($this->never())
            ->method('dispatch');

        $this->im->add(File::create());
    }

    /**
     * @test
     * @dataProvider provideAddableObjects
     */
    public function addingAnAlreadyExistingObjectShouldReturnFalse(Identifiable $object)
    {
        $this->ed
            ->expects($this->exactly(2))
            ->method('dispatch');

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
    public function addManyAddsAllObjectsAndRewindsIterator()
    {
        $first = File::create(array('id' => 6));
        $array = array(
            $first,
            Folder::create(array('id' => 6)),
            File::create(array('id' => 6)),
        );
        $iter = new ArrayIterator($array);

        $im = $this
            ->getMockBuilder('Xi\Filelib\IdentityMap\IdentityMap')
            ->setMethods(array('add'))
            ->disableOriginalConstructor()
            ->getMock();

        $im
            ->expects($this->exactly(3))
            ->method('add')
            ->with(
                $this->isInstanceOf('Xi\Filelib\IdentityMap\Identifiable')
            );

        $im->addMany($iter);

        $this->assertSame($first, $iter->current());
    }

    /**
     * @test
     */
    public function removeManyDeletesAllObjectsAndRewindsIterator()
    {
        $first = File::create(array('id' => 6));
        $array = array(
            $first,
            Folder::create(array('id' => 6)),
            File::create(array('id' => 6)),
        );
        $iter = new ArrayIterator($array);

        $im = $this
            ->getMockBuilder('Xi\Filelib\IdentityMap\IdentityMap')
            ->setMethods(array('remove'))
            ->disableOriginalConstructor()
            ->getMock();

        $im
            ->expects($this->exactly(3))
            ->method('remove')
            ->with($this->isInstanceOf('Xi\Filelib\IdentityMap\Identifiable'));

        $im->removeMany($iter);
        $this->assertSame($first, $iter->current());
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
        $this->ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(
                Events::IDENTITYMAP_BEFORE_ADD,
                $this->isInstanceOf('Xi\Filelib\Event\IdentifiableEvent')
            );

        $this->ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                Events::IDENTITYMAP_AFTER_ADD,
                $this->isInstanceOf('Xi\Filelib\Event\IdentifiableEvent')
            );

        $this->im->add($object);
        $this->assertSame($object, $this->im->get($object->getId(), get_class($object)));
    }

    /**
     * @test
     * @dataProvider provideAddableObjects
     */
    public function removeShouldRemoveObject(Identifiable $object)
    {
        $this->ed
            ->expects($this->at(2))
            ->method('dispatch')
            ->with(
                Events::IDENTITYMAP_BEFORE_REMOVE,
                $this->isInstanceOf('Xi\Filelib\Event\IdentifiableEvent')
            );

        $this->ed
            ->expects($this->at(3))
            ->method('dispatch')
            ->with(
                Events::IDENTITYMAP_AFTER_REMOVE,
                $this->isInstanceOf('Xi\Filelib\Event\IdentifiableEvent')
            );

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
