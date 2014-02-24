<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\File;

use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Backend\Finder\FileFinder;
use ArrayIterator;
use Xi\Filelib\File\FileProfile;
use Xi\Filelib\Events;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;

class FileOperatorTest extends \Xi\Filelib\Tests\TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $foop;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $commander;

    /**
     * @var FileOperator
     */
    private $op;

    public function setUp()
    {
        $this->commander = $this->getMockedCommander();
        $this->backend = $this->getMockedBackend();
        $this->ed = $this->getMockedEventDispatcher();
        $this->foop = $this->getMockedFolderOperator();

        $this->filelib = $this->getMockedFilelib(null, null, $this->foop, null, $this->ed, $this->backend, $this->commander);

        $this->op = new FileOperator();
        $this->op->attachTo($this->filelib);

    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\File\FileOperator');
    }

    public function provideUploads()
    {
        return array(
            array(new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg')),
            array(ROOT_TESTS . '/data/self-lussing-manatee.jpg')
        );
    }

    /**
     * @test
     * @dataProvider provideUploads
     */
    public function uploadCreatesExecutableAndExecutes($upload)
    {
        $folder = $this->getMockedFolder();
        $profile = 'versioned';

        $command = $this->getMockedExecutable('xoo');
        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                FileOperator::COMMAND_UPLOAD,
                $this->isType('array')
            )
            ->will($this->returnValue($command));

        $this->foop->expects($this->never())->method('findRoot');

        $this->op->upload($upload, $folder, $profile);

    }

    /**
     * @test
     */
    public function uploadShouldFindRootFolderIfNoFolderIsSupplied()
    {
        $folder = $this->getMockedFolder();
        $profile = 'versioned';

        $command = $this->getMockedExecutable('xoo');
        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                FileOperator::COMMAND_UPLOAD,
                $this->isType('array')
            )
            ->will($this->returnValue($command));

        $this->foop->expects($this->once())->method('findRoot')->will($this->returnValue($folder));

        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $this->op->upload($upload, null, $profile);
    }


    /**
     * @test
     */
    public function addProfileShouldAddProfile()
    {
        $this->assertEquals(array(), $this->op->getProfiles());

        $profile = $this->getMockedFileProfile('xooxer');

        $profile2 = $this->getMockedFileProfile('lusser');

        $this->ed
            ->expects($this->exactly(2))
            ->method('addSubscriber')
            ->with($this->isInstanceOf('Xi\Filelib\File\FileProfile'));

        $this->ed
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                $this->equalTo(Events::PROFILE_AFTER_ADD),
                $this->isInstanceOf('Xi\Filelib\Event\FileProfileEvent')
            );

        $this->op->addProfile($profile);
        $this->assertCount(1, $this->op->getProfiles());

        $this->op->addProfile($profile2);
        $this->assertCount(2, $this->op->getProfiles());

        $this->assertSame($profile, $this->op->getProfile('xooxer'));
        $this->assertSame($profile2, $this->op->getProfile('lusser'));
    }

    /**
     * @test
     */
    public function addProfileShouldFailWhenProfileAlreadyExists()
    {
        $profile = new FileProfile('xooxer');
        $profile2 = new FileProfile('xooxer');

        $this->op->addProfile($profile);
        $this->setExpectedException('Xi\Filelib\InvalidArgumentException');
        $this->op->addProfile($profile2);
    }

    /**
     * @test
     */
    public function getProfileShouldFailWhenProfileDoesNotExist()
    {
        $this->setExpectedException('Xi\Filelib\InvalidArgumentException');
        $prof = $this->op->getProfile('xooxer');
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
            ->with($id, 'Xi\Filelib\File\File')
            ->will($this->returnValue(false));


        $file = $this->op->find($id);
        $this->assertEquals(false, $file);
    }

    /**
     * @test
     */
    public function findShouldReturnFileInstanceIfFileIsFound()
    {
        $id = 1;

        $file = new File();

        $this->backend
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($id))
            ->will($this->returnValue($file));

        $ret = $this->op->find($id);
        $this->assertSame($file, $ret);
    }

    /**
     * @test
     */
    public function findByFilenameShouldReturnFalseIfFileIsNotFound()
    {
        $folder = Folder::create(array('id' => 6));

        $finder = new FileFinder(
            array(
                'folder_id' => 6,
                'name' => 'lussname',
            )
        );

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue(new ArrayIterator(array())));

        $ret = $this->op->findByFilename($folder, 'lussname');
        $this->assertFalse($ret);
    }

    /**
     * @test
     */
    public function findByFilenameShouldReturnFileInstanceIfFileIsFound()
    {
        $id = 1;

        $folder = Folder::create(array('id' => 6));

        $file = new File();

        $finder = new FileFinder(
            array(
                'folder_id' => 6,
                'name' => 'lussname',
            )
        );

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue(new ArrayIterator(array($file))));

        $ret = $this->op->findByFilename($folder, 'lussname');
        $this->assertSame($file, $ret);
    }

      /**
     * @test
     */
    public function findAllShouldReturnEmptyIfNoFilesAreFound()
    {
        $finder = new FileFinder();

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')
            ->with($this->equalTo($finder))
            ->will($this->returnValue(new ArrayIterator(array())));

        $files = $this->op->findAll();
        $this->assertCount(0, $files);

    }

    /**
     * @test
     */
    public function findAllShouldReturnAnArrayOfFileInstancesIfFilesAreFound()
    {
        $finder = new FileFinder();

        $iter = new ArrayIterator(array(
            new File(),
            new File(),
            new File(),
        ));

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
            $this->equalTo($finder)
        )
            ->will($this->returnValue($iter));

        $files = $this->op->findAll();
        $this->assertSame($iter, $files);
    }

    /**
     * @test
     */
    public function hasVersionShouldDelegateToProfile()
    {
        $file = File::create(array('profile' => 'meisterlus'));

        $profile = $this->getMockedFileProfile('meisterlus');
        $profile
            ->expects($this->once())
            ->method('fileHasVersion')
            ->with(
                $file,
                'kloo'
            )
            ->will($this->returnValue(true));

        $this->op->addProfile($profile);
        $hasVersion = $this->op->hasVersion($file, 'kloo');
        $this->assertTrue($hasVersion);
    }

    /**
     * @test
     */
    public function getVersionProviderShouldDelegateToProfile()
    {
        $vp = $this->getMockedVersionProvider('lux');
        $file = File::create(array('profile' => 'meisterlus'));

        $profile = $this->getMockedFileProfile('meisterlus');
        $profile
            ->expects($this->once())
            ->method('getVersionProvider')
            ->with(
                $file,
                'kloo'
            )
            ->will($this->returnValue($vp));

        $this->op->addProfile($profile);
        $ret = $this->op->getVersionProvider($file, 'kloo');

        $this->assertSame($vp, $ret);
    }

    /**
     * @test
     */
    public function updateCreatesExecutableAndExecutes()
    {
        $file = File::create();
        $command = $this->getMockedCommand('topic', 'xoo');

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                FileOperator::COMMAND_UPDATE,
                array(
                    $file
                )
            )
            ->will($this->returnValue($command));


        $this->op->update($file);
    }

    /**
     * @test
     */
    public function copyCreatesExecutableAndExecutes()
    {
        $file = File::create();
        $folder = Folder::create();
        $command = $this->getMockedCommand('topic', 'xoo');

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                FileOperator::COMMAND_COPY,
                array(
                    $file,
                    $folder
                )
            )
            ->will($this->returnValue($command));

        $this->op->copy($file, $folder);
    }

    /**
     * @test
     */
    public function deleteCreatesExecutableAndExecutes()
    {
        $file = File::create();
        $command = $this->getMockedCommand('topic', 'xoo');

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                FileOperator::COMMAND_DELETE,
                array(
                    $file
                )
            )
            ->will($this->returnValue($command));


        $this->op->delete($file);
    }

}
