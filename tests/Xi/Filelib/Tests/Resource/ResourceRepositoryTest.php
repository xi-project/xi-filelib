<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\File;

use Xi\Filelib\Backend\Finder\ResourceFinder;
use Xi\Filelib\File\File;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Collections\Collection\ArrayCollection;
use Xi\Filelib\Resource\ResourceRepository;

class ResourceRepositoryTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filelib;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $backend;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $ed;

    /**
     * @var ResourceRepository
     */
    private $rere;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $foop;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $commander;

    public function setUp()
    {
        $this->commander = $this->getMockedCommander();
        $this->backend = $this->getMockedBackend();
        $this->ed = $this->getMockedEventDispatcher();


        $this->filelib = $this->getMockedFilelib(
            null,
            array(
                'backend' => $this->backend,
                'ed' => $this->ed,
                'commander' => $this->commander,
            )
        );

        $this->rere = new ResourceRepository();
        $this->rere->attachTo($this->filelib);
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
     */
    public function findShouldReturnFalseIfFileIsNotFound()
    {
        $id = 1;

        $this->backend
            ->expects($this->once())
            ->method('findById')
            ->with($id, 'Xi\Filelib\Resource\Resource')
            ->will($this->returnValue(false));

        $file = $this->rere->find($id);
        $this->assertEquals(false, $file);
    }

    /**
     * @test
     */
    public function findShouldReturnResourceIfFound()
    {
        $id = 1;

        $resource = Resource::create();

        $this->backend
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($id))
            ->will($this->returnValue($resource));

        $ret = $this->rere->find($id);
        $this->assertSame($resource, $ret);
    }

    /**
     * @test
     */
    public function findAllShouldReturnEmptyIfNoFilesAreFound()
    {
        $finder = new ResourceFinder();

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')
            ->with($this->equalTo($finder))
            ->will($this->returnValue(ArrayCollection::create(array())));

        $files = $this->rere->findAll();
        $this->assertCount(0, $files);

    }

    /**
     * @test
     */
    public function findAllShouldReturnAnArrayOfResourcesIfFound()
    {
        $finder = new ResourceFinder();

        $iter = ArrayCollection::create(array(
            Resource::create(),
            Resource::create(),
            Resource::create(),
        ));

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue($iter));

        $files = $this->rere->findAll();
        $this->assertSame($iter, $files);
    }

    /**
     * @test
     */
    public function updateCreatesExecutableAndExecutes()
    {
        $resource = Resource::create();
        $command = $this->getMockedCommand('topic', 'xoo');

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                ResourceRepository::COMMAND_UPDATE,
                array(
                    $resource
                )
            )
            ->will($this->returnValue($command));


        $this->rere->update($resource);
    }

    /**
     * @test
     */
    public function deleteCreatesExecutableAndExecutes()
    {
        $resource = Resource::create();
        $command = $this->getMockedCommand('topic', 'xoo');

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                ResourceRepository::COMMAND_DELETE,
                array(
                    $resource
                )
            )
            ->will($this->returnValue($command));

        $this->rere->delete($resource);
    }

    /**
     * @test
     */
    public function createCreatesExecutableAndExecutes()
    {
        $resource = Resource::create();
        $command = $this->getMockedCommand('topic', 'xoo');

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                ResourceRepository::COMMAND_CREATE,
                array(
                    $resource,
                    'tussenhofenlussenmeister',
                )
            )
            ->will($this->returnValue($command));

        $this->rere->create($resource, 'tussenhofenlussenmeister');
    }


    /**
     * @test
     */
    public function findResourceForUploadShouldGenerateNewResourceIfProfileAllowsButNoResourceIsFound()
    {
        $file = File::create(array('profile' => 'lussenhof'));

        $op = $this->getMockedFileRepository();
        $backend = $this->getMockedBackend();

        $profile = $this->getMockedFileProfile();

        $pm = $this->getMockedProfileManager(array('lussenhof'));
        $pm->expects($this->any())->method('getProfile')
            ->with($this->equalTo('lussenhof'))
            ->will($this->returnValue($profile));

        $profile->expects($this->never())
            ->method('isSharedResourceAllowed');

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpeg';
        $profile = 'lussenhof';
        $upload = new FileUpload($path);
        $hash = sha1_file($upload->getRealPath());

        $finder = new ResourceFinder(
            array(
                'hash' => $hash,
            )
        );
        $backend->expects($this->once())->method('findByFinder')
            ->with($this->equalTo($finder))
            ->will($this->returnValue(ArrayCollection::create(array())));

        $folder = $this->getMockedFolder();

        $filelib = $this->getMockedFilelib(
            null,
            array(
                'backend' => $backend,
                'pm' => $pm,
                'commander' => $this->commander
            )
        );

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(ResourceRepository::COMMAND_CREATE)
            ->will($this->returnValue($this->getMockedExecutable(true)));

        $rere = new ResourceRepository();
        $rere->attachTo($filelib);
        $ret = $rere->findResourceForUpload($file, $upload);

        $this->assertInstanceOf('Xi\Filelib\Resource\Resource', $ret);
        $this->assertSame($hash, $ret->getHash());
    }

    /**
     * @test
     */
    public function findResourceForUploadShouldGenerateNewResourceIfProfileRequires()
    {
        $file = File::create(array('profile' => 'lussenhof'));

        $op = $this->getMockedFileRepository();

        $backend = $this->getMockedBackend();

        $profile = $this->getMockedFileProfile();

        $pm = $this->getMockedProfileManager();
        $pm->expects($this->any())->method('getProfile')
            ->with($this->equalTo('lussenhof'))
            ->will($this->returnValue($profile));

        $profile->expects($this->atLeastOnce())
            ->method('isSharedResourceAllowed')
            ->will($this->returnValue(false));

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpeg';
        $profile = 'lussenhof';
        $upload = new FileUpload($path);
        $hash = sha1_file($upload->getRealPath());

        $finder = new ResourceFinder(
            array(
                'hash' => $hash,
            )
        );
        $backend->expects($this->once())->method('findByFinder')
            ->with($this->equalTo($finder))
            ->will($this->returnValue(ArrayCollection::create(array(
                Resource::create(array('id' => 'first-id')),
                Resource::create(array('id' => 'second-id')),
            ))));

        $folder = $this->getMockedFolder();

        $filelib = $this->getMockedFilelib(
            null,
            array(
                'backend' => $backend,
                'pm' => $pm,
                'commander' => $this->commander
            )
        );

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(ResourceRepository::COMMAND_CREATE)
            ->will($this->returnValue($this->getMockedExecutable(true)));

        $rere = new ResourceRepository();
        $rere->attachTo($filelib);
        $ret = $rere->findResourceForUpload($file, $upload);

        $this->assertInstanceOf('Xi\Filelib\Resource\Resource', $ret);
        $this->assertSame($hash, $ret->getHash());
    }

    /**
     * @test
     */
    public function findResourceForUploadShouldReuseResourceIfProfileAllowsAndResourcesAreFound()
    {
        $file = File::create(array('profile' => 'lussenhof'));

        $op = $this->getMockedFileRepository();

        $profile = $this->getMockedFileProfile();

        $pm = $this->getMockedProfileManager();
        $pm->expects($this->any())->method('getProfile')
            ->with($this->equalTo('lussenhof'))
            ->will($this->returnValue($profile));

        $profile->expects($this->once())
            ->method('isSharedResourceAllowed')
            ->will($this->returnValue(true));

        $backend = $this->getMockedBackend();

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpeg';
        $profile = 'lussenhof';
        $upload = new FileUpload($path);
        $hash = sha1_file($upload->getRealPath());

        $finder = new ResourceFinder(
            array(
                'hash' => $hash,
            )
        );
        $backend->expects($this->once())->method('findByFinder')
            ->with($this->equalTo($finder))
            ->will($this->returnValue(ArrayCollection::create(array(
                Resource::create(array('id' => 'first-id')),
                Resource::create(array('id' => 'second-id')),
            ))));

        $folder = $this->getMockedFolder();

        $filelib = $this->getMockedFilelib(
            null,
            array(
                'backend' => $backend,
                'pm' => $pm,
                'commander' => $this->commander
            )
        );

        $rere = new ResourceRepository();
        $rere->attachTo($filelib);
        $ret = $rere->findResourceForUpload($file, $upload);

        $this->assertInstanceOf('Xi\Filelib\Resource\Resource', $ret);
        $this->assertSame('first-id', $ret->getId());
    }
}
