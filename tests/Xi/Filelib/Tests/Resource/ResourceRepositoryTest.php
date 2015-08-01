<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\File;

use Prophecy\Argument;
use Prophecy\Prophecy\ProphecyInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Xi\Filelib\Backend\Finder\ResourceFinder;
use Xi\Filelib\Events;
use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Profile\FileProfile;
use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Upload\FileUpload;
use PhpCollection\Sequence;
use Xi\Filelib\Resource\ResourceRepository;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\Storage\Adapter\MemoryStorageAdapter;

class ResourceRepositoryTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $backend;

    /**
     * @var ProphecyInterface
     */
    private $ed;

    /**
     * @var ResourceRepository
     */
    private $op;

    public function setUp()
    {
        $this->ed = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->filelib = $this->getFilelib(true);
        $this->op = $this->filelib->getResourceRepository();
    }

    private function getFilelib($mockedEventDispatcher)
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter(),
            ($mockedEventDispatcher) ? $this->ed->reveal() : new EventDispatcher()
        );

        $filelib->addProfile(new FileProfile(
            'tussi', false
        ));

        return $filelib;
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Resource\ResourceRepository');
    }

    /**
     * @test
     * @group naama
     */
    public function finds()
    {
        $this->assertFalse($this->op->find('xoo-xoo-xoo'));

        $resource = ConcreteResource::create([
            'id' => 'xoo-xoo-xoo'
        ]);

        $resource = $this->op->create(
            $resource,
            ROOT_TESTS . '/data/self-lussing-manatee.jpg'
        );

        $this->assertInstanceOf('Xi\Filelib\Resource\ConcreteResource', $resource);

        $this->assertInstanceOf(
            'Xi\Filelib\Resource\ConcreteResource',
            $this->op->find($resource->getId())
        );
    }

    /**
     * @test
     */
    public function findsAll()
    {
        $this->assertCount(0, $this->op->findAll());

        $this->op->create(
            ConcreteResource::create([
                'id' => 'xoo-xoo-xoo'
            ]),
            ROOT_TESTS . '/data/self-lussing-manatee.jpg'
        );

        $this->op->create(
            ConcreteResource::create([
                'id' => 'lus-lus-lus'
            ]),
            ROOT_TESTS . '/data/self-lussing-manatee.jpg'
        );

        $this->assertCount(2, $this->op->findAll());
    }

    /**
     * @test
     */
    public function creates()
    {
        $resource = ConcreteResource::create([
            'id' => 'xoo-xoo-xoo'
        ]);

        $res = $this->op->create(
            $resource,
            ROOT_TESTS . '/data/self-lussing-manatee.jpg'
        );

        $this->assertSame($res, $resource);

        $this->ed->dispatch(
            Events::RESOURCE_BEFORE_CREATE,
            Argument::type('Xi\Filelib\Event\ResourceEvent')
        )->shouldHaveBeenCalled();

        $this->ed->dispatch(
            Events::RESOURCE_AFTER_CREATE,
            Argument::type('Xi\Filelib\Event\ResourceEvent')
        )->shouldHaveBeenCalled();


        $this->assertTrue($this->filelib->getStorage()->exists($resource));
    }

    /**
     * @test
     */
    public function deletes()
    {
        $resource = ConcreteResource::create([
            'id' => 'xoo-xoo-xoo'
        ]);

        $res = $this->op->create(
            $resource,
            ROOT_TESTS . '/data/self-lussing-manatee.jpg'
        );

        $this->assertSame($res, $resource);

        $this->assertNotFalse($this->op->find($res->getId()));
        $this->assertNotFalse($this->filelib->getStorage()->exists($res));

        $this->op->delete($res);

        $this->assertFalse($this->filelib->getStorage()->exists($res));

        $this->ed->dispatch(
            Events::RESOURCE_BEFORE_DELETE,
            Argument::type('Xi\Filelib\Event\ResourceEvent')
        )->shouldHaveBeenCalled();

        $this->ed->dispatch(
            Events::RESOURCE_AFTER_DELETE,
            Argument::type('Xi\Filelib\Event\ResourceEvent')
        )->shouldHaveBeenCalled();

    }

    /**
     * @test
     */
    public function updates()
    {
        $resource = ConcreteResource::create([
            'id' => 'xoo-xoo-xoo'
        ]);

        $res = $this->op->create(
            $resource,
            ROOT_TESTS . '/data/self-lussing-manatee.jpg'
        );

        $this->assertSame($res, $resource);

        $this->assertNotFalse($this->op->find($res->getId()));
        $this->assertNotFalse($this->filelib->getStorage()->exists($resource));

        $this->op->update($res);

        $this->ed->dispatch(
            Events::RESOURCE_BEFORE_UPDATE,
            Argument::type('Xi\Filelib\Event\ResourceEvent')
        )->shouldHaveBeenCalled();

        $this->ed->dispatch(
            Events::RESOURCE_AFTER_UPDATE,
            Argument::type('Xi\Filelib\Event\ResourceEvent')
        )->shouldHaveBeenCalled();
    }


    /**
     * @test
     */
    public function findResourceForUploadCreatesNewResourceOnlyOnce()
    {
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $filelib = $this->getFilelib(false);
        $op = $filelib->getResourceRepository();

        $file = File::create([
            'profile' => 'default'
        ]);

        $resource = $op->findResourceForUpload($file, new FileUpload($path));

        $this->assertInstanceOf(
            'Xi\Filelib\Resource\ConcreteResource',
            $resource
        );

        $this->assertSame(
            $resource,
            $op->findResourceForUpload($file, new FileUpload($path))
        );
    }

    /**
     * @test
     */
    public function findResourceForUploadCreatesNewResourceIfProfileDemands()
    {
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $filelib = $this->getFilelib(false);
        $op = $filelib->getResourceRepository();

        $file1 = File::create([
            'profile' => 'default'
        ]);

        $file2 = File::create([
            'profile' => 'tussi'
        ]);

        $resource1 = $op->findResourceForUpload($file1, new FileUpload($path));
        $resource2 = $op->findResourceForUpload($file2, new FileUpload($path));

        $this->assertInstanceOf(
            'Xi\Filelib\Resource\ConcreteResource',
            $resource2
        );

        $this->assertNotSame(
            $resource1,
            $op->findResourceForUpload($file2, new FileUpload($path))
        );

        $this->assertNotSame(
            $resource2,
            $op->findResourceForUpload($file2, new FileUpload($path))
        );
    }
}
