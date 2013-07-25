<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\File;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\EnqueueableCommand;
use Xi\Filelib\Backend\Finder\FileFinder;
use ArrayIterator;
use Xi\Filelib\File\FileProfile;
use Xi\Filelib\Events;

class FileOperatorTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\FileOperator'));
    }

    /**
     * @test
     */
    public function strategiesShouldDefaultToSynchronous()
    {
        $filelib = $this->getMockedFilelib();
        $op = new FileOperator($filelib);

        $this->assertEquals(EnqueueableCommand::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_UPLOAD));
        $this->assertEquals(EnqueueableCommand::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_AFTERUPLOAD));
        $this->assertEquals(EnqueueableCommand::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_UPDATE));
        $this->assertEquals(EnqueueableCommand::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_DELETE));
        $this->assertEquals(EnqueueableCommand::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_COPY));
    }

    /**
     * @test
     */
    public function settingAndGettingCommandStrategiesShouldWork()
    {
        $filelib = $this->getMockedFilelib();
        $op = new FileOperator($filelib);

        $this->assertEquals(EnqueueableCommand::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_UPLOAD));

        $this->assertSame($op, $op->setCommandStrategy(FileOperator::COMMAND_UPLOAD, EnqueueableCommand::STRATEGY_ASYNCHRONOUS));

        $this->assertEquals(EnqueueableCommand::STRATEGY_ASYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_UPLOAD));

    }

    /**
     * @test
     */
    public function uploadShouldExecuteCommandsWhenSynchronousStrategyIsUsed()
    {
        $filelib = $this->getMockedFilelib();

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $profile = 'versioned';

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

        $op->expects($this->never())->method('getQueue');

        $uploadCommand = $this->getMockBuilder('Xi\Filelib\File\Command\UploadFileCommand')
                               ->setConstructorArgs(array($upload, $folder, $profile))
                               ->setMethods(array('execute'))
                               ->getMock();

        $uploadCommand->expects($this->once())->method('execute');

        $op->expects($this->at(0))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UploadFileCommand'))->will($this->returnValue($uploadCommand));

        $op->upload($upload, $folder, $profile);

    }

    /**
     * @test
     */
    public function uploadShouldQueueUploadCommandWhenAynchronousStrategyIsUsed()
    {
        $filelib = $this->getMockedFilelib();

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $profile = 'versioned';

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

        $queue = $this->getMockForAbstractClass('Xi\Filelib\Queue\Queue');
        $op->expects($this->atLeastOnce())->method('getQueue')->will($this->returnValue($queue));

        $uploadCommand = $this->getMockBuilder('Xi\Filelib\File\Command\UploadFileCommand')
                               ->setConstructorArgs(array($upload, $folder, $profile))
                               ->setMethods(array('execute'))
                               ->getMock();

        $queue->expects($this->once())->method('enqueue')->with($this->isInstanceOf('Xi\Filelib\File\Command\UploadFileCommand'));

        $op->expects($this->at(0))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UploadFileCommand'))->will($this->returnValue($uploadCommand));

        $op->setCommandStrategy(FileOperator::COMMAND_UPLOAD, EnqueueableCommand::STRATEGY_ASYNCHRONOUS);

        $op->upload($upload, $folder, $profile);

    }

    /**
     * @test
     */
    public function addProfileShouldAddProfile()
    {
        $filelib = $this->getMockedFilelib();
        $op = new FileOperator($filelib);

        $eventDispatcher = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($eventDispatcher));

        $this->assertEquals(array(), $op->getProfiles());

        $profile = $this->getMockedFileProfile();
        $profile->expects($this->any())->method('getIdentifier')->will($this->returnValue('xooxer'));

        $profile2 = $this->getMockedFileProfile();
        $profile2->expects($this->any())->method('getIdentifier')->will($this->returnValue('lusser'));

        $eventDispatcher->expects($this->exactly(2))->method('addSubscriber')->with($this->isInstanceOf('Xi\Filelib\File\FileProfile'));

        $eventDispatcher->expects($this->exactly(2))->method('dispatch')
                        ->with($this->equalTo(Events::PROFILE_AFTER_ADD), $this->isInstanceOf('Xi\Filelib\Event\FileProfileEvent'));

        $op->addProfile($profile);
        $this->assertCount(1, $op->getProfiles());

        $op->addProfile($profile2);
        $this->assertCount(2, $op->getProfiles());

        $this->assertSame($profile, $op->getProfile('xooxer'));
        $this->assertSame($profile2, $op->getProfile('lusser'));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function addProfileShouldFailWhenProfileAlreadyExists()
    {
        $profile = new FileProfile('xooxer');
        $profile2 = new FileProfile('xooxer');

        $filelib = $this->getMockedFilelib();
        $eventDispatcher = $this->getMockedEventDispatcher();
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($eventDispatcher));

        $op = new FileOperator($filelib);

        $op->addProfile($profile);
        $op->addProfile($profile2);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function getProfileShouldFailWhenProfileDoesNotExist()
    {
       $filelib = $this->getMockedFilelib();
       $op = new FileOperator($filelib);

       $prof = $op->getProfile('xooxer');
    }

    /**
     * @test
     */
    public function findShouldReturnFalseIfFileIsNotFound()
    {
        $id = 1;

        $filelib = $this->getMockedFilelib();

        $backend = $this->getMockedBackend();
        $op = $this->getFileOperatorWithMockedBackend($filelib, $backend);

        $backend
            ->expects($this->once())
            ->method('findById')
            ->with($id, 'Xi\Filelib\File\File')
            ->will($this->returnValue(false));


        $file = $op->find($id);
        $this->assertEquals(false, $file);
    }

    /**
     * @test
     */
    public function findShouldReturnFileInstanceIfFileIsFound()
    {
        $id = 1;

        $filelib = $this->getMockedFilelib();
        $backend = $this->getMockedBackend();
        $op = $this->getFileOperatorWithMockedBackend($filelib, $backend);

        $file = new File();

        $backend
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($id))
            ->will($this->returnValue($file));

        $ret = $op->find($id);
        $this->assertSame($file, $ret);
    }

    /**
     * @test
     */
    public function findByFilenameShouldReturnFalseIfFileIsNotFound()
    {
        $filelib = $this->getMockedFilelib();
        $backend = $this->getMockedBackend();
        $op = $this->getFileOperatorWithMockedBackend($filelib, $backend);

        $folder = Folder::create(array('id' => 6));

        $finder = new FileFinder(
            array(
                'folder_id' => 6,
                'name' => 'lussname',
            )
        );

        $backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue(new ArrayIterator(array())));

        $ret = $op->findByFilename($folder, 'lussname');
        $this->assertFalse($ret);
    }

    /**
     * @test
     */
    public function findByFilenameShouldReturnFileInstanceIfFileIsFound()
    {
        $filelib = $this->getMockedFilelib();
        $backend = $this->getMockedBackend();
        $op = $this->getFileOperatorWithMockedBackend($filelib, $backend);

        $id = 1;

        $folder = Folder::create(array('id' => 6));

        $file = new File();

        $finder = new FileFinder(
            array(
                'folder_id' => 6,
                'name' => 'lussname',
            )
        );

        $backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue(new ArrayIterator(array($file))));

        $ret = $op->findByFilename($folder, 'lussname');
        $this->assertSame($file, $ret);
    }

      /**
     * @test
     */
    public function findAllShouldReturnEmptyIfNoFilesAreFound()
    {
        $filelib = $this->getMockedFilelib();
        $backend = $this->getMockedBackend();
        $op = $this->getFileOperatorWithMockedBackend($filelib, $backend);

        $finder = new FileFinder();

        $backend
            ->expects($this->once())
            ->method('findByFinder')
            ->with($this->equalTo($finder))
            ->will($this->returnValue(new ArrayIterator(array())));

        $files = $op->findAll();
        $this->assertCount(0, $files);

    }

    /**
     * @test
     */
    public function findAllShouldReturnAnArrayOfFileInstancesIfFilesAreFound()
    {

        $filelib = $this->getMockedFilelib();
        $backend = $this->getMockedBackend();
        $op = $this->getFileOperatorWithMockedBackend($filelib, $backend);

        $finder = new FileFinder();

        $iter = new ArrayIterator(array(
            new File(),
            new File(),
            new File(),
        ));

        $backend
            ->expects($this->once())
            ->method('findByFinder')->with(
            $this->equalTo($finder)
        )
            ->will($this->returnValue($iter));

        $files = $op->findAll();

        $this->assertSame($iter, $files);
    }

    /**
     * @test
     */
    public function prepareUploadShouldReturnFileUpload()
    {
        $filelib = $this->getMockedFilelib();
        $op = new FileOperator($filelib);

        $upload = $op->prepareUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $this->assertInstanceOf('Xi\Filelib\File\Upload\FileUpload', $upload);

    }

    /**
     * @test
     */
    public function hasVersionShouldDelegateToProfile()
    {
        $filelib = $this->getMockedFilelib();
        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile'))
                   ->getMock();

         $file = File::create(array('profile' => 'meisterlus'));

         $profile = $this->getMockedFileProfile();
         $profile->expects($this->once())->method('fileHasVersion')->with($this->equalTo($file), $this->equalTo('kloo'));

         $op->expects($this->any())->method('getProfile')->with($this->equalTo('meisterlus'))->will($this->returnValue($profile));

         $hasVersion = $op->hasVersion($file, 'kloo');

    }

    /**
     * @test
     */
    public function getVersionProviderShouldDelegateToProfile()
    {
        $filelib = $this->getMockedFilelib();
        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile'))
                   ->getMock();

         $file = File::create(array('profile' => 'meisterlus'));

         $profile = $this->getMockedFileProfile();
         $profile->expects($this->once())->method('getVersionProvider')->with($this->equalTo($file), $this->equalTo('kloo'));

         $op->expects($this->any())->method('getProfile')->with($this->equalTo('meisterlus'))->will($this->returnValue($profile));

         $vp = $op->getVersionProvider($file, 'kloo');

    }

    /**
     * @test
     */
    public function addProfileShouldDelegateToProfile()
    {

        $filelib = $this->getMockedFilelib();
        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile'))
                   ->getMock();

        $plugin = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $plugin->expects($this->atLeastOnce())->method('getProfiles')->will($this->returnValue(array('lussi', 'tussi', 'jussi')));

        $profile1 = $this->getMockedFileProfile();
        $profile1->expects($this->once())->method('addPlugin')->with($this->equalTo($plugin));

        $profile2 = $this->getMockedFileProfile();
        $profile2->expects($this->once())->method('addPlugin')->with($this->equalTo($plugin));

        $profile3 = $this->getMockedFileProfile();
        $profile3->expects($this->once())->method('addPlugin')->with($this->equalTo($plugin));

        $op->expects($this->exactly(3))->method('getProfile')
           ->with($this->logicalOr($this->equalTo('lussi'), $this->equalTo('tussi'), $this->equalTo('jussi')))
           ->will($this->returnValueMap(
                array(
                    array('tussi', $profile1),
                    array('lussi', $profile2),
                    array('jussi', $profile3),
                )
            ));

        $op->addPlugin($plugin);

    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function settingInvalidstrategyShouldThrowException()
    {
        $filelib = $this->getMockedFilelib();
        $op = new FileOperator($filelib);
        $op->setCommandStrategy(FileOperator::COMMAND_UPLOAD, 'tussenhof');

    }

    /**
    * @test
    */
    public function getFolderOperatorShouldDelegateToFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->once())->method('getFolderOperator');
        $op = new FileOperator($filelib);
        $op->getFolderOperator();
    }

    public function provideCommandMethods()
    {
        return array(
            array('Xi\Filelib\File\Command\CopyFileCommand', 'copy', FileOperator::COMMAND_COPY, EnqueueableCommand::STRATEGY_ASYNCHRONOUS, true, true),
            array('Xi\Filelib\File\Command\CopyFileCommand', 'copy', FileOperator::COMMAND_COPY, EnqueueableCommand::STRATEGY_SYNCHRONOUS, false, true),
            array('Xi\Filelib\File\Command\DeleteFileCommand', 'delete', FileOperator::COMMAND_DELETE, EnqueueableCommand::STRATEGY_ASYNCHRONOUS, true, false),
            array('Xi\Filelib\File\Command\DeleteFileCommand', 'delete', FileOperator::COMMAND_DELETE, EnqueueableCommand::STRATEGY_SYNCHRONOUS, false, false),
            array('Xi\Filelib\File\Command\UpdateFileCommand', 'update', FileOperator::COMMAND_UPDATE, EnqueueableCommand::STRATEGY_ASYNCHRONOUS, true, false),
            array('Xi\Filelib\File\Command\UpdateFileCommand', 'update', FileOperator::COMMAND_UPDATE, EnqueueableCommand::STRATEGY_SYNCHRONOUS, false, false),
        );

    }

    /**
     * @test
     * @dataProvider provideCommandMethods
     */
    public function commandMethodsShouldExecuteOrEnqeueDependingOnStrategy($commandClass, $operatorMethod, $commandName, $strategy, $queueExpected, $fileAndFolder)
    {
         $filelib = $this->getMockedFilelib();

          $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

          $queue = $this->getMockForAbstractClass('Xi\Filelib\Queue\Queue');
          $op->expects($this->any())->method('getQueue')->will($this->returnValue($queue));

          $command = $this->getMockBuilder($commandClass)
                          ->disableOriginalConstructor()
                          ->setMethods(array('execute'))
                          ->getMock();

          if ($queueExpected) {
              $queue->expects($this->once())->method('enqueue')->with($this->isInstanceOf($commandClass));
              $command->expects($this->never())->method('execute');
          } else {
              $queue->expects($this->never())->method('enqueue');
              $command->expects($this->once())->method('execute');
          }

          $file = $this->getMock('Xi\Filelib\File\File');
          $folder = $this->getMock('Xi\Filelib\Folder\Folder');

          $op->expects($this->once())->method('createCommand')->with($this->equalTo($commandClass))->will($this->returnValue($command));

          $op->setCommandStrategy($commandName, $strategy);

          if ($fileAndFolder) {
              $op->$operatorMethod($file, $folder);
          } else {
              $op->$operatorMethod($file);
          }

    }

    /**
     * @param $filelib
     * @param $backend
     *
     * @return FileOperator
     */
    protected function getFileOperatorWithMockedBackend($filelib, $backend)
    {
        $fiop = $this
            ->getMockBuilder('Xi\Filelib\File\FileOperator')
            ->setConstructorArgs(array($filelib))
            ->setMethods(array('getBackend'))
            ->getMock();

        $fiop->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        return $fiop;
    }


}
