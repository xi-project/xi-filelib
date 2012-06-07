<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\File;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\DefaultFileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
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

        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_UPLOAD));
        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_AFTERUPLOAD));
        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_UPDATE));
        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_DELETE));
        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_PUBLISH));
        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_UNPUBLISH));
        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_COPY));
    }

    /**
     * @test
     */
    public function settingAndGettingCommandStrategiesShouldWork()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);

        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_UPLOAD));

        $this->assertSame($op, $op->setCommandStrategy(FileOperator::COMMAND_UPLOAD, Command::STRATEGY_ASYNCHRONOUS));

        $this->assertEquals(Command::STRATEGY_ASYNCHRONOUS, $op->getCommandStrategy(FileOperator::COMMAND_UPLOAD));

    }

    /**
     * @test
     */
    public function uploadShouldExecuteCommandsWhenSynchronousStrategyIsUsed()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

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

        $uploadCommand->expects($this->once())->method('execute');

        $op->expects($this->at(0))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UploadFileCommand'))->will($this->returnValue($uploadCommand));

        $op->upload($upload, $folder, $profile);

    }

    /**
     * @test
     */
    public function uploadShouldQueueUploadCommandWhenAynchronousStrategyIsUsed()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');
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

        $queue->expects($this->once())->method('enqueue')->with($this->isInstanceOf('Xi\Filelib\File\Command\UploadFileCommand'));

        $op->expects($this->at(0))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UploadFileCommand'))->will($this->returnValue($uploadCommand));

        $op->setCommandStrategy(FileOperator::COMMAND_UPLOAD, Command::STRATEGY_ASYNCHRONOUS);

        $op->upload($upload, $folder, $profile);

    }

    /**
     * @test
     */
    public function getInstanceShouldReturnAnInstanceOfFileWithNoData()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);

        $file = $op->getInstance();
        $this->assertInstanceOf('Xi\Filelib\File\File', $file);

    }



    /**
     * @test
     */
    public function getInstanceShouldReturnAnInstanceOfFileWithData()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);

        $data = array(
            'name' => 'larva-consumes-newspaper.jpg',
        );

        $file = $op->getInstance($data);
        $this->assertInstanceOf('Xi\Filelib\File\File', $file);

        $this->assertEquals('larva-consumes-newspaper.jpg', $file->getName());

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

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->any())->method('getIdentifier')->will($this->returnValue('xooxer'));

        $profile2 = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile2->expects($this->any())->method('getIdentifier')->will($this->returnValue('lusser'));

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
        $eventDispatcher = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->setEventDispatcher($eventDispatcher);
        $op = new DefaultFileOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFile')->with($this->equalTo($id))->will($this->returnValue(
            array(
                'id' => $id,
                'filename' => 'lussen.hof',
            )
        ));

        $filelib->setBackend($backend);

        $eventDispatcher->expects($this->once())->method('dispatch')->with($this->equalTo('file.instantiate'));

        $file = $op->find($id);
        $this->assertInstanceOf('Xi\Filelib\File\File', $file);
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

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

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
        $eventDispatcher = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->setEventDispatcher($eventDispatcher);
        $op = new DefaultFileOperator($filelib);

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

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

        $eventDispatcher->expects($this->once())->method('dispatch')->with($this->equalTo('file.instantiate'));

        $file = $op->findByFilename($folder, $id);
        $this->assertInstanceOf('Xi\Filelib\File\File', $file);
        $this->assertEquals($id, $file->getId());

    }


      /**
     * @test
     */
    public function findAllShouldReturnEmptyIfNoFilesAreFound()
    {
        $id = 1;

        $filelib = new FileLibrary();
        $eventDispatcher = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->setEventDispatcher($eventDispatcher);
        $op = new DefaultFileOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findAllFiles')->will($this->returnValue(array()));

        $filelib->setBackend($backend);

        $eventDispatcher->expects($this->never())->method('dispatch');

        $files = $op->findAll();
        $this->assertEquals(array(), $files);

    }


    /**
     * @test
     */
    public function findAllShouldReturnAnArrayOfFileInstancesIfFilesAreFound()
    {

        $filelib = new FileLibrary();
        $eventDispatcher = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->setEventDispatcher($eventDispatcher);

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

        $eventDispatcher->expects($this->exactly(3))->method('dispatch')->with($this->equalTo('file.instantiate'));

        $files = $op->findAll();

        $this->assertInternalType('array', $files);

        $this->assertCount(3, $files);

        $file = $files[1];

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);

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

    /**
     * @test
     */
    public function typeResolverShouldDefaultToStupid()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);

        $this->assertInstanceOf('Xi\Filelib\Tool\TypeResolver\StupidTypeResolver', $op->getTypeResolver());

    }

    /**
     *  @test
     */
    public function typeResolverShouldRespectSetter()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);

        $typeResolver = $this->getMock('Xi\Filelib\Tool\TypeResolver\TypeResolver');

        $op->setTypeResolver($typeResolver);
        $this->assertSame($typeResolver, $op->getTypeResolver());
    }



    /**
     * @test
     */
    public function getTypeShouldDelegateToTypeResolver()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);

        $typeResolver = $this->getMock('Xi\Filelib\Tool\TypeResolver\TypeResolver');
        $typeResolver->expects($this->once())->method('resolveType')
                     ->with($this->equalTo('application/lus'));

        $file = File::create(array(
            'name' => 'larvador.lus',
            'resource' => Resource::create(array('mimetype' => 'application/lus'))
        ));

        $op->setTypeResolver($typeResolver);
        $op->getType($file);

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

         $file = File::create(array('profile' => 'meisterlus'));

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

         $file = File::create(array('profile' => 'meisterlus'));

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

        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $op->setCommandStrategy(FileOperator::COMMAND_UPLOAD, 'tussenhof');

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


    /**
     * @test
     */
    public function getInstanceAndTriggerEventShouldDelegateToGetInstanceAndTriggerCorrectEvent()
    {

        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                         ->setMethods(array('getInstance', 'getEventDispatcher'))
                         ->disableOriginalConstructor()
                         ->getMock();

        $file = $this->getMock('Xi\Filelib\File\File');
        $eventDispatcher = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $data = array(
            'id' => 1,
        );


        $op->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($eventDispatcher));
        $op->expects($this->once())->method('getInstance')->with($this->equalTo($data))->will($this->returnValue($file));

        $eventDispatcher->expects($this->once())->method('dispatch')->with($this->equalTo('file.instantiate'), $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $ret = $op->getInstanceAndTriggerEvent($data);

        $this->assertInstanceOf('Xi\Filelib\File\File', $ret);

    }

    public function provideCommandMethods()
    {
        return array(
            array('Xi\Filelib\File\Command\CopyFileCommand', 'copy', FileOperator::COMMAND_COPY, Command::STRATEGY_ASYNCHRONOUS, true, true),
            array('Xi\Filelib\File\Command\CopyFileCommand', 'copy', FileOperator::COMMAND_COPY, Command::STRATEGY_SYNCHRONOUS, false, true),
            array('Xi\Filelib\File\Command\DeleteFileCommand', 'delete', FileOperator::COMMAND_DELETE, Command::STRATEGY_ASYNCHRONOUS, true, false),
            array('Xi\Filelib\File\Command\DeleteFileCommand', 'delete', FileOperator::COMMAND_DELETE, Command::STRATEGY_SYNCHRONOUS, false, false),
            array('Xi\Filelib\File\Command\PublishFileCommand', 'publish', FileOperator::COMMAND_PUBLISH, Command::STRATEGY_ASYNCHRONOUS, true, false),
            array('Xi\Filelib\File\Command\PublishFileCommand', 'publish', FileOperator::COMMAND_PUBLISH, Command::STRATEGY_SYNCHRONOUS, false, false),
            array('Xi\Filelib\File\Command\UnpublishFileCommand', 'unpublish', FileOperator::COMMAND_UNPUBLISH, Command::STRATEGY_ASYNCHRONOUS, true, false),
            array('Xi\Filelib\File\Command\UnpublishFileCommand', 'unpublish', FileOperator::COMMAND_UNPUBLISH, Command::STRATEGY_SYNCHRONOUS, false, false),
            array('Xi\Filelib\File\Command\UpdateFileCommand', 'update', FileOperator::COMMAND_UPDATE, Command::STRATEGY_ASYNCHRONOUS, true, false),
            array('Xi\Filelib\File\Command\UpdateFileCommand', 'update', FileOperator::COMMAND_UPDATE, Command::STRATEGY_SYNCHRONOUS, false, false),
        );


    }

    /**
     * @test
     * @dataProvider provideCommandMethods
     */
    public function commandMethodsShouldExecuteOrEnqeueDependingOnStrategy($commandClass, $operatorMethod, $commandName, $strategy, $queueExpected, $fileAndFolder)
    {

         $filelib = $this->getMock('Xi\Filelib\FileLibrary');

          $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
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



}
