<?php

namespace Xi\Tests\Filelib\File;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\DefaultFileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileItem;
use Xi\Filelib\Folder\FolderItem;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Command;

class DefaultFileOperatorTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\DefaultFileOperator'));
    }


    /**
     * @test
     */
    public function strategiesShouldDefaultToSynchronous()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);

        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(DefaultFileOperator::COMMAND_UPLOAD));
        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(DefaultFileOperator::COMMAND_AFTERUPLOAD));
        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(DefaultFileOperator::COMMAND_UPDATE));
        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(DefaultFileOperator::COMMAND_DELETE));
        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(DefaultFileOperator::COMMAND_PUBLISH));
        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(DefaultFileOperator::COMMAND_UNPUBLISH));

    }



    /**
     * @test
     */
    public function settingAndGettingCommandStrategiesShouldWork()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);

        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(DefaultFileOperator::COMMAND_UPLOAD));

        $this->assertSame($op, $op->setCommandStrategy(DefaultFileOperator::COMMAND_UPLOAD, Command::STRATEGY_ASYNCHRONOUS));

        $this->assertEquals(Command::STRATEGY_ASYNCHRONOUS, $op->getCommandStrategy(DefaultFileOperator::COMMAND_UPLOAD));

    }


    /**
     * @test
     */
    public function uploadShouldExecuteCommandsWhenSynchronousStrategyIsUsed()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $folder = $this->getMockForAbstractClass('Xi\Filelib\Folder\Folder');
        $file = $this->getMockForAbstractClass('Xi\Filelib\File\File');
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $profile = 'versioned';

        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

        $op->expects($this->never())->method('getQueue');

        $uploadCommand = $this->getMockBuilder('Xi\Filelib\File\Command\UploadFileCommand')
                               ->setConstructorArgs(array($op, $upload, $folder, $profile))
                               ->setMethods(array('execute'))
                               ->getMock();

        $afterUploadCommand = $this->getMockBuilder('Xi\Filelib\File\Command\AfterUploadFileCommand')
                               ->disableOriginalConstructor()
                               ->setMethods(array('execute'))
                               ->getMock();

        // $afterUploadCommand = $this->getMockBuilder('Xi\Filelib\File\Command\AfterUploadFileCommand')->disableOriginalConstructor()->getMock();

        $uploadCommand->expects($this->once())->method('execute')->will($this->returnValue($afterUploadCommand));
        $afterUploadCommand->expects($this->once())->method('execute')->will($this->returnValue('luss'));

        $op->expects($this->at(0))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UploadFileCommand'))->will($this->returnValue($uploadCommand));
        // $op->expects($this->at(1))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\AfterUploadFileCommand'))->will($this->returnValue($afterUploadCommand));


        $op->upload($upload, $folder, $profile);

    }


        /**
     * @test
     */
    public function uploadShouldExecuteUploadAndQueueAfterUploadWhenSynchronousAndAsynchronousStrategiesAreUsed()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $folder = $this->getMockForAbstractClass('Xi\Filelib\Folder\Folder');
        $file = $this->getMockForAbstractClass('Xi\Filelib\File\File');
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $profile = 'versioned';

        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

        $queue = $this->getMockForAbstractClass('Xi\Filelib\Queue\Queue');
        $queue->expects($this->once())->method('enqueue')->with($this->isInstanceOf('Xi\Filelib\Queue\Message'));

        $op->expects($this->atLeastOnce())->method('getQueue')->will($this->returnValue($queue));

        $uploadCommand = $this->getMockBuilder('Xi\Filelib\File\Command\UploadFileCommand')
                               ->setConstructorArgs(array($op, $upload, $folder, $profile))
                               ->setMethods(array('execute'))
                               ->getMock();

        $afterUploadCommand = $this->getMockBuilder('Xi\Filelib\File\Command\AfterUploadFileCommand')
                               ->disableOriginalConstructor()
                               ->setMethods(array('execute'))
                               ->getMock();

        // $afterUploadCommand = $this->getMockBuilder('Xi\Filelib\File\Command\AfterUploadFileCommand')->disableOriginalConstructor()->getMock();

        $uploadCommand->expects($this->once())->method('execute')->will($this->returnValue($afterUploadCommand));
        $afterUploadCommand->expects($this->never())->method('execute');

        $op->expects($this->at(0))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UploadFileCommand'))->will($this->returnValue($uploadCommand));
        // $op->expects($this->at(1))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\AfterUploadFileCommand'))->will($this->returnValue($afterUploadCommand));

        $op->setCommandStrategy(DefaultFileOperator::COMMAND_AFTERUPLOAD, Command::STRATEGY_ASYNCHRONOUS);
        $op->upload($upload, $folder, $profile);

    }




    /**
     * @test
     */
    public function uploadShouldQueueUploadCommandWhenAynchronousStrategyIsUsed()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $folder = $this->getMockForAbstractClass('Xi\Filelib\Folder\Folder');
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $profile = 'versioned';

        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

        $queue = $this->getMockForAbstractClass('Xi\Filelib\Queue\Queue');
        $op->expects($this->atLeastOnce())->method('getQueue')->will($this->returnValue($queue));

        $uploadCommand = $this->getMockBuilder('Xi\Filelib\File\Command\UploadFileCommand')
                               ->setConstructorArgs(array($op, $upload, $folder, $profile))
                               ->setMethods(array('execute'))
                               ->getMock();

        $queue->expects($this->once())->method('enqueue')->with($this->isInstanceOf('Xi\Filelib\Queue\Message'));

        $op->expects($this->at(0))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UploadFileCommand'))->will($this->returnValue($uploadCommand));

        $op->setCommandStrategy(DefaultFileOperator::COMMAND_UPLOAD, Command::STRATEGY_ASYNCHRONOUS);

        $op->upload($upload, $folder, $profile);

    }

    /**
     * @test
     */
    public function updateShouldExecuteActionWhenSynchronousStrategyIsUsed()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');

          $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

          $op->expects($this->never())->method('getQueue');

          $file = $this->getMockForAbstractClass('Xi\Filelib\File\File');

          $updateCommand = $this->getMockBuilder('Xi\Filelib\File\Command\UpdateFileCommand')
                               ->setConstructorArgs(array($op, $file))
                               ->setMethods(array('execute'))
                               ->getMock();

          $updateCommand->expects($this->once())->method('execute');

          $op->expects($this->once())->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UpdateFileCommand'))->will($this->returnValue($updateCommand));

          $op->update($file);

    }


    /**
     * @test
     */
    public function updateShouldEnqueueActionWhenAsynchronousStrategyIsUsed()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');

          $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

          $queue = $this->getMockForAbstractClass('Xi\Filelib\Queue\Queue');
          $queue->expects($this->once())->method('enqueue')->with($this->isInstanceOf('Xi\Filelib\Queue\Message'));

          $op->expects($this->once())->method('getQueue')->will($this->returnValue($queue));

          $file = $this->getMockForAbstractClass('Xi\Filelib\File\File');

          $updateCommand = $this->getMockBuilder('Xi\Filelib\File\Command\UpdateFileCommand')
                               ->setConstructorArgs(array($op, $file))
                               ->setMethods(array('execute'))
                               ->getMock();

          $updateCommand->expects($this->never())->method('execute');

          $op->expects($this->once())->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UpdateFileCommand'))->will($this->returnValue($updateCommand));

          $op->setCommandStrategy(DefaultFileOperator::COMMAND_UPDATE, Command::STRATEGY_ASYNCHRONOUS);
          $op->update($file);

    }


    /**
     * @test
     */
    public function publishShouldExecuteActionWhenSynchronousStrategyIsUsed()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');

          $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

          $op->expects($this->never())->method('getQueue');

          $file = $this->getMockForAbstractClass('Xi\Filelib\File\File');

          $command = $this->getMockBuilder('Xi\Filelib\File\Command\PublishFileCommand')
                               ->setConstructorArgs(array($op, $file))
                               ->setMethods(array('execute'))
                               ->getMock();

          $command->expects($this->once())->method('execute');

          $op->expects($this->once())->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\PublishFileCommand'))->will($this->returnValue($command));

          $op->publish($file);

    }


    /**
     * @test
     */
    public function publishShouldEnqueueActionWhenAsynchronousStrategyIsUsed()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');

          $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

          $queue = $this->getMockForAbstractClass('Xi\Filelib\Queue\Queue');
          $queue->expects($this->once())->method('enqueue')->with($this->isInstanceOf('Xi\Filelib\Queue\Message'));

          $op->expects($this->once())->method('getQueue')->will($this->returnValue($queue));

          $file = $this->getMockForAbstractClass('Xi\Filelib\File\File');

          $command = $this->getMockBuilder('Xi\Filelib\File\Command\PublishFileCommand')
                               ->setConstructorArgs(array($op, $file))
                               ->setMethods(array('execute'))
                               ->getMock();

          $command->expects($this->never())->method('execute');

          $op->expects($this->once())->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\PublishFileCommand'))->will($this->returnValue($command));

          $op->setCommandStrategy(DefaultFileOperator::COMMAND_PUBLISH, Command::STRATEGY_ASYNCHRONOUS);
          $op->publish($file);

    }


    /**
     * @test
     */
    public function unpublishShouldExecuteActionWhenSynchronousStrategyIsUsed()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');

          $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

          $op->expects($this->never())->method('getQueue');

          $file = $this->getMockForAbstractClass('Xi\Filelib\File\File');

          $command = $this->getMockBuilder('Xi\Filelib\File\Command\UnpublishFileCommand')
                               ->setConstructorArgs(array($op, $file))
                               ->setMethods(array('execute'))
                               ->getMock();

          $command->expects($this->once())->method('execute');

          $op->expects($this->once())->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UnpublishFileCommand'))->will($this->returnValue($command));

          $op->unpublish($file);

    }




    /**
     * @test
     */
    public function unpublishShouldEnqueueActionWhenAsynchronousStrategyIsUsed()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');

          $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

          $queue = $this->getMockForAbstractClass('Xi\Filelib\Queue\Queue');
          $queue->expects($this->once())->method('enqueue')->with($this->isInstanceOf('Xi\Filelib\Queue\Message'));

          $op->expects($this->once())->method('getQueue')->will($this->returnValue($queue));

          $file = $this->getMockForAbstractClass('Xi\Filelib\File\File');

          $command = $this->getMockBuilder('Xi\Filelib\File\Command\UnpublishFileCommand')
                               ->setConstructorArgs(array($op, $file))
                               ->setMethods(array('execute'))
                               ->getMock();

          $command->expects($this->never())->method('execute');

          $op->expects($this->once())->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UnpublishFileCommand'))->will($this->returnValue($command));

          $op->setCommandStrategy(DefaultFileOperator::COMMAND_UNPUBLISH, Command::STRATEGY_ASYNCHRONOUS);
          $op->unpublish($file);

    }


        /**
     * @test
     */
    public function deleteShouldExecuteActionWhenSynchronousStrategyIsUsed()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');

          $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

          $op->expects($this->never())->method('getQueue');

          $file = $this->getMockForAbstractClass('Xi\Filelib\File\File');

          $command = $this->getMockBuilder('Xi\Filelib\File\Command\DeleteFileCommand')
                               ->setConstructorArgs(array($op, $file))
                               ->setMethods(array('execute'))
                               ->getMock();

          $command->expects($this->once())->method('execute');

          $op->expects($this->once())->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\DeleteFileCommand'))->will($this->returnValue($command));

          $op->delete($file);

    }




    /**
     * @test
     */
    public function deleteShouldEnqueueActionWhenAsynchronousStrategyIsUsed()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');

          $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setMethods(array('createCommand', 'getQueue'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

          $queue = $this->getMockForAbstractClass('Xi\Filelib\Queue\Queue');
          $queue->expects($this->once())->method('enqueue')->with($this->isInstanceOf('Xi\Filelib\Queue\Message'));

          $op->expects($this->once())->method('getQueue')->will($this->returnValue($queue));

          $file = $this->getMockForAbstractClass('Xi\Filelib\File\File');

          $command = $this->getMockBuilder('Xi\Filelib\File\Command\DeleteFileCommand')
                               ->setConstructorArgs(array($op, $file))
                               ->setMethods(array('execute'))
                               ->getMock();

          $command->expects($this->never())->method('execute');

          $op->expects($this->once())->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\DeleteFileCommand'))->will($this->returnValue($command));

          $op->setCommandStrategy(DefaultFileOperator::COMMAND_DELETE, Command::STRATEGY_ASYNCHRONOUS);
          $op->delete($file);

    }



    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);

        $val = 'Lussen\Hofer';
        $this->assertEquals('Xi\Filelib\File\FileItem', $op->getClass());
        $this->assertSame($op, $op->setClass($val));
        $this->assertEquals($val, $op->getClass());

        /*
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $this->assertEquals(null, $profile->getFilelib());
        $this->assertSame($profile, $profile->setFilelib($filelib));
        $this->assertSame($filelib, $profile->getFilelib());
        */
    }

    /**
     * @test
     */
    public function getInstanceShouldReturnAnInstanceOfConfiguredClassWithNoData()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);

        $mockClass = $this->getMockClass('Xi\Filelib\File\FileItem');

        $file = $op->getInstance();
        $this->assertInstanceOf('Xi\Filelib\File\FileItem', $file);

        $op->setClass($mockClass);

        $file = $op->getInstance();
        $this->assertInstanceOf($mockClass, $file);

    }



    /**
     * @test
     */
    public function getInstanceShouldReturnAnInstanceOfConfiguredClassWithData()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);

        $data = array(
            'mimetype' => 'luss/xoo'
        );

        $file = $op->getInstance($data);
        $this->assertInstanceOf('Xi\Filelib\File\FileItem', $file);

        $this->assertEquals('luss/xoo', $file->getMimetype());

    }

    /**
     * @test
     */
    public function addProfileShouldAddProfile()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);

        $eventDispatcher = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($eventDispatcher));

        $this->assertEquals(array(), $op->getProfiles());

        $linker = $this->getMockForAbstractClass('Xi\Filelib\Linker\Linker');
        $linker->expects($this->once())->method('setFilelib')->with($this->equalTo($filelib));

        $linker2 = $this->getMockForAbstractClass('Xi\Filelib\Linker\Linker');
        $linker2->expects($this->once())->method('setFilelib')->with($this->equalTo($filelib));

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->any())->method('getIdentifier')->will($this->returnValue('xooxer'));
        $profile->expects($this->any())->method('getLinker')->will($this->returnValue($linker));

        $profile2 = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile2->expects($this->any())->method('getIdentifier')->will($this->returnValue('lusser'));
        $profile2->expects($this->any())->method('getLinker')->will($this->returnValue($linker2));

        $eventDispatcher->expects($this->exactly(2))->method('addSubscriber')->with($this->isInstanceOf('Xi\Filelib\File\FileProfile'));

        $eventDispatcher->expects($this->exactly(2))->method('dispatch')
                        ->with($this->equalTo('fileprofile.add'), $this->isInstanceOf('Xi\Filelib\Event\FileProfileEvent'));

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
        $linker = $this->getMockForAbstractClass('Xi\Filelib\Linker\Linker');

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->any())->method('getIdentifier')->will($this->returnValue('xooxer'));
        $profile->expects($this->any())->method('getLinker')->will($this->returnValue($linker));

        $profile2 = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile2->expects($this->any())->method('getIdentifier')->will($this->returnValue('xooxer'));
        $profile2->expects($this->any())->method('getLinker')->will($this->returnValue($linker));

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $eventDispatcher = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($eventDispatcher));

        $op = new DefaultFileOperator($filelib);

        $op->addProfile($profile);
        $op->addProfile($profile2);
    }


    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function getProfileShouldFailWhenProfileDoesNotExist()
    {
       $filelib = $this->getMock('Xi\Filelib\FileLibrary');
       $op = new DefaultFileOperator($filelib);

       $prof = $op->getProfile('xooxer');
    }



    /**
     * @test
     */
    public function findShouldReturnFalseIfFileIsNotFound()
    {
        $id = 1;

        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFile')->with($this->equalTo($id))->will($this->returnValue(false));

        $filelib->setBackend($backend);

        $file = $op->find($id);
        $this->assertEquals(false, $file);

    }


    /**
     * @test
     */
    public function findShouldReturnFileInstanceIfFileIsFound()
    {
        $id = 1;

        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFile')->with($this->equalTo($id))->will($this->returnValue(
            array(
                'id' => $id,
                'filename' => 'lussen.hof',
            )
        ));

        $filelib->setBackend($backend);

        $file = $op->find($id);
        $this->assertInstanceOf('Xi\Filelib\File\FileItem', $file);
        $this->assertEquals($id, $file->getId());

    }



    /**
     * @test
     */
    public function findByFilenameShouldReturnFalseIfFileIsNotFound()
    {
        $id = 1;

        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);

        $folder = $this->getMockForAbstractClass('Xi\Filelib\Folder\Folder');

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFileByFilename')->with(
            $this->equalTo($folder),
            $this->equalTo($id)
        )->will($this->returnValue(false));

        $filelib->setBackend($backend);

        $file = $op->findByFilename($folder, $id);
        $this->assertEquals(false, $file);

    }


    /**
     * @test
     */
    public function findByFilenameShouldReturnFileInstanceIfFileIsFound()
    {
        $id = 1;

        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);

        $folder = $this->getMockForAbstractClass('Xi\Filelib\Folder\Folder');

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFileByFilename')->with(
            $this->equalTo($folder),
            $this->equalTo($id)
        )->will($this->returnValue(
            array(
                'id' => $id,
                'filename' => 'lussen.hof',
            )
        ));

        $filelib->setBackend($backend);

        $file = $op->findByFilename($folder, $id);
        $this->assertInstanceOf('Xi\Filelib\File\FileItem', $file);
        $this->assertEquals($id, $file->getId());

    }


      /**
     * @test
     */
    public function findAllShouldReturnEmptyIfNoFilesAreFound()
    {
        $id = 1;

        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findAllFiles')->will($this->returnValue(array()));

        $filelib->setBackend($backend);

        $files = $op->findAll();
        $this->assertEquals(array(), $files);

    }


    /**
     * @test
     */
    public function findAllShouldReturnAnArrayOfFileInstancesIfFilesAreFound()
    {

        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findAllFiles')->will($this->returnValue(
            array(
                array(
                    'id' => 1,
                    'name' => 'lussen.hof',
                ),
                array(
                    'id' => 2,
                    'name' => 'lussen.tus',
                ),
                array(
                    'id' => 2,
                    'name' => 'lussen.xoo',
                )
            )
        ));

        $filelib->setBackend($backend);

        $files = $op->findAll();

        $this->assertInternalType('array', $files);

        $this->assertCount(3, $files);

        $file = $files[1];

        $this->assertInstanceOf('Xi\Filelib\File\FileItem', $file);

        $this->assertEquals('lussen.tus', $file->getName());

    }


    /**
     * @test
     */
    public function prepareUploadShouldReturnFileUpload()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);

        $upload = $op->prepareUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $this->assertInstanceOf('Xi\Filelib\File\Upload\FileUpload', $upload);

    }




    public function provideMimetypes()
    {
        return array(
            array('image', 'image/jpeg'),
            array('video', 'video/lus'),
            array('document', 'document/pdf'),
        );
    }

    /**
     * @test
     * @dataProvider provideMimetypes
     */
    public function getTypeShouldReturnCorrectType($expected, $mimetype)
    {
        $file = FileItem::create(array('mimetype' => $mimetype));

        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);

        $this->assertEquals($expected, $op->getType($file));

    }

    /**
     * @test
     */
    public function hasVersionShouldDelegateToProfile()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile'))
                   ->getMock();

         $file = FileItem::create(array('profile' => 'meisterlus'));

         $profile = $this->getMock('Xi\Filelib\File\FileProfile');
         $profile->expects($this->once())->method('fileHasVersion')->with($this->equalTo($file), $this->equalTo('kloo'));

         $op->expects($this->any())->method('getProfile')->with($this->equalTo('meisterlus'))->will($this->returnValue($profile));

         $hasVersion = $op->hasVersion($file, 'kloo');

    }


    /**
     * @test
     */
    public function getVersionProviderShouldDelegateToProfile()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile'))
                   ->getMock();

         $file = FileItem::create(array('profile' => 'meisterlus'));

         $profile = $this->getMock('Xi\Filelib\File\FileProfile');
         $profile->expects($this->once())->method('getVersionProvider')->with($this->equalTo($file), $this->equalTo('kloo'));

         $op->expects($this->any())->method('getProfile')->with($this->equalTo('meisterlus'))->will($this->returnValue($profile));

         $vp = $op->getVersionProvider($file, 'kloo');

    }






    /**
     * @test
     */
    public function addProfileShouldDelegateToProfile()
    {

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile'))
                   ->getMock();


        $plugin = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $plugin->expects($this->atLeastOnce())->method('getProfiles')->will($this->returnValue(array('lussi', 'tussi', 'jussi')));


        $profile1 = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile1->expects($this->once())->method('addPlugin')->with($this->equalTo($plugin));

        $profile2 = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile2->expects($this->once())->method('addPlugin')->with($this->equalTo($plugin));

        $profile3 = $this->getMock('Xi\Filelib\File\FileProfile');
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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $op->setCommandStrategy(DefaultFileOperator::COMMAND_UPLOAD, 'tussenhof');

    }

    /**
    * @test
    */
    public function getFolderOperatorShouldDelegateToFilelib()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $filelib->expects($this->once())->method('getFolderOperator');

        $op = new DefaultFileOperator($filelib);

        $op->getFolderOperator();

    }





}