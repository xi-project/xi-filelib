<?php

namespace Xi\Tests\Filelib\Folder;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\DefaultFolderOperator;
use Xi\Filelib\Folder\FolderItem;
use Xi\Filelib\Command;

class DefaultFolderOperatorTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\DefaultFolderOperator'));
    }


    /**
     * @test
     */
    public function strategiesShouldDefaultToSynchronous()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFolderOperator($filelib);

        $this->assertEquals(Command::STRATEGY_SYNCHRONOUS, $op->getCommandStrategy(DefaultFolderOperator::COMMAND_CREATE));

    }


    public function provideCommandMethods()
    {
        return array(
            array('Xi\Filelib\Folder\Command\DeleteFolderCommand', 'delete', DefaultFolderOperator::COMMAND_DELETE, Command::STRATEGY_ASYNCHRONOUS, true),
            array('Xi\Filelib\Folder\Command\DeleteFolderCommand', 'delete', DefaultFolderOperator::COMMAND_DELETE, Command::STRATEGY_SYNCHRONOUS, false),
            array('Xi\Filelib\Folder\Command\CreateFolderCommand', 'create', DefaultFolderOperator::COMMAND_CREATE, Command::STRATEGY_ASYNCHRONOUS, true),
            array('Xi\Filelib\Folder\Command\CreateFolderCommand', 'create', DefaultFolderOperator::COMMAND_CREATE, Command::STRATEGY_SYNCHRONOUS, false),
            array('Xi\Filelib\Folder\Command\UpdateFolderCommand', 'update', DefaultFolderOperator::COMMAND_UPDATE, Command::STRATEGY_ASYNCHRONOUS, true),
            array('Xi\Filelib\Folder\Command\UpdateFolderCommand', 'update', DefaultFolderOperator::COMMAND_UPDATE, Command::STRATEGY_SYNCHRONOUS, false),
            array('Xi\Filelib\Folder\Command\CreateByUrlFolderCommand', 'createByUrl', DefaultFolderOperator::COMMAND_CREATE_BY_URL, Command::STRATEGY_ASYNCHRONOUS, true),
            array('Xi\Filelib\Folder\Command\CreateByUrlFolderCommand', 'createByUrl', DefaultFolderOperator::COMMAND_CREATE_BY_URL, Command::STRATEGY_SYNCHRONOUS, false),


        );


    }


    /**
     * @test
     * @dataProvider provideCommandMethods
     */
    public function commandMethodsShouldExecuteOrEnqeueDependingOnStrategy($commandClass, $operatorMethod, $commandName, $strategy, $queueExpected)
    {

         $filelib = $this->getMock('Xi\Filelib\FileLibrary');

          $op = $this->getMockBuilder('Xi\Filelib\Folder\DefaultFolderOperator')
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
              $queue->expects($this->once())->method('enqueue')->with($this->isInstanceOf('Xi\Filelib\Queue\Message'));
              $command->expects($this->never())->method('execute');
          } else {
              $queue->expects($this->never())->method('enqueue');
              $command->expects($this->once())->method('execute');
          }

          $folder = $this->getMockForAbstractClass('Xi\Filelib\Folder\Folder');

          $op->expects($this->once())->method('createCommand')->with($this->equalTo($commandClass))->will($this->returnValue($command));

          $op->setCommandStrategy($commandName, $strategy);
          $op->$operatorMethod($folder);

    }



    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFolderOperator($filelib);

        $val = 'Lussen\Hofer';
        $this->assertEquals('Xi\Filelib\Folder\FolderItem', $op->getClass());
        $this->assertSame($op, $op->setClass($val));
        $this->assertEquals($val, $op->getClass());
    }

    /**
     * @test
     */
    public function findShouldReturnFalseIfFileIsNotFound()
    {
        $id = 1;

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFolder')->with($this->equalTo($id))->will($this->returnValue(false));

        $filelib->setBackend($backend);

        $folder = $op->find($id);
        $this->assertFalse($folder);
    }

    /**
     * @test
     */
    public function findShouldReturnFolderInstanceIfFileIsFound()
    {
        $id = 1;

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFolder')->with($this->equalTo($id))->will($this->returnValue(
                        array(
                            'id' => $id,
                            'parent_id' => null,
                        )
                ));

        $filelib->setBackend($backend);

        $folder = $op->find($id);
        $this->assertInstanceOf('Xi\Filelib\Folder\FolderItem', $folder);
        $this->assertEquals($id, $folder->getId());
        $this->assertEquals(null, $folder->getParentId());
    }

    /**
     * @test
     */
    public function findFilesShouldReturnEmptyArrayIteratorWhenNoFilesAreFound()
    {
        $filelib = new FileLibrary();
        $op = $this->getMockBuilder('Xi\Filelib\Folder\DefaultFolderOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getFileOperator'))
                   ->getMock();


        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFilesIn')->with($this->equalTo($folder))->will($this->returnValue(
                        array(
                        )
                ));

        $filelib->setBackend($backend);

        $files = $op->findFiles($folder);

        $this->assertInstanceOf('ArrayIterator', $files);

        $this->assertCount(0, $files);
    }

    /**
     * @test
     */
    public function findFilesShouldReturnNonEmptyArrayIteratorWhenFilesAreFound()
    {
        $filelib = new FileLibrary();

        $op = $this->getMockBuilder('Xi\Filelib\Folder\DefaultFolderOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getFileOperator'))
                   ->getMock();


        $fiop = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                     ->disableOriginalConstructor()
                     ->getMock();


        $fiop->expects($this->exactly(3))->method('getInstanceAndTriggerEvent')->will($this->returnValue($this->getMockForAbstractClass('Xi\Filelib\File\File')));

        $op->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFilesIn')->with($this->equalTo($folder))->will($this->returnValue(
                        array(
                            array('id' => 1, 'mimetype' => 'lus/xoo'),
                            array('id' => 2, 'mimetype' => 'lus/xoo'),
                            array('id' => 3, 'mimetype' => 'lus/tus'),
                        )
                ));

        $filelib->setBackend($backend);

        $files = $op->findFiles($folder);

        $this->assertInstanceOf('ArrayIterator', $files);

        $this->assertCount(3, $files);

        $file = $files->current();

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);
        
    }

    /**
     * @test
     */
    public function findParentFolderShouldReturnFalseWhenParentIdIsNull()
    {
        $id = null;

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->never())->method('findFolder');

        $filelib->setBackend($backend);

        $folder = FolderItem::create(array('parent_id' => $id));

        $parent = $op->findParentFolder($folder);

        $this->assertFalse($parent);
    }

    /**
     * @test
     */
    public function findParentFolderShouldReturnFalseWhenParentIsNotFound()
    {
        $id = 5;

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFolder')->with($this->equalTo($id))->will($this->returnValue(
                        false
                ));

        $filelib->setBackend($backend);

        $folder = FolderItem::create(array('parent_id' => $id));

        $parent = $op->findParentFolder($folder);

        $this->assertFalse($parent);
    }

    /**
     * @test
     */
    public function findParentFolderShouldReturnFolderWhenParentIsFound()
    {
        $id = 5;

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFolder')->with($this->equalTo($id))->will($this->returnValue(
                        array('id' => 5, 'parent_id' => 6)
                ));

        $filelib->setBackend($backend);

        $folder = FolderItem::create(array('parent_id' => $id));

        $parent = $op->findParentFolder($folder);

        $this->assertInstanceOf('Xi\Filelib\Folder\FolderItem', $folder);
    }

    /**
     * @test
     */
    public function getInstanceShouldReturnAnInstanceOfConfiguredClassWithNoData()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFolderOperator($filelib);

        $mockClass = $this->getMockClass('Xi\Filelib\Folder\FolderItem');

        $folder = $op->getInstance();
        $this->assertInstanceOf('Xi\Filelib\Folder\FolderItem', $folder);

        $op->setClass($mockClass);

        $folder = $op->getInstance();
        $this->assertInstanceOf($mockClass, $folder);
    }

    /**
     * @test
     */
    public function getInstanceShouldReturnAnInstanceOfConfiguredClassWithData()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFolderOperator($filelib);

        $data = array(
            'name' => 'manatee'
        );

        $folder = $op->getInstance($data);
        $this->assertInstanceOf('Xi\Filelib\Folder\FolderItem', $folder);

        $this->assertEquals('manatee', $folder->getName());
    }


    /**
     * @test
     */
    public function findSubFoldersShouldReturnEmptyArrayIteratorWhenNoSubFoldersAreFound()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findSubFolders')->with($this->equalTo($folder))->will($this->returnValue(
                        array(
                        )
                ));

        $filelib->setBackend($backend);

        $folders = $op->findSubFolders($folder);

        $this->assertInstanceOf('ArrayIterator', $folders);

        $this->assertCount(0, $folders);
    }

    /**
     * @test
     */
    public function findSubFoldersShouldReturnNonEmptyArrayIteratorWhenSubFoldersAreFound()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findSubFolders')->with($this->equalTo($folder))->will($this->returnValue(
                        array(
                            array('id' => 433, 'parent_id' => null),
                            array('id' => 24, 'parent_id' => 1),
                            array('id' => 3, 'parent_id' => 2),
                        )
                ));

        $filelib->setBackend($backend);

        $folders = $op->findSubFolders($folder);

        $this->assertInstanceOf('ArrayIterator', $folders);

        $this->assertCount(3, $folders);

        $folders->next();
        $folder = $folders->current();

        $this->assertEquals(24, $folder->getId());
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
    }


    /**
     * @test
     */
    public function findByUrlShouldReturnFalseWhenFolderIsNotFound()
    {
        $id = 'lussen/tussi';

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFolderByUrl')->with($this->equalTo($id))->will($this->returnValue(
                        false
                ));

        $filelib->setBackend($backend);

        $folder = $op->findByUrl($id);

        $this->assertFalse($folder);
    }

    /**
     * @test
     */
    public function findByUrlShouldReturnFolderWhenFolderIsFound()
    {
        $id = 'lussen/tussi';

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFolderByUrl')->with($this->equalTo($id))->will($this->returnValue(
                        array('url' => 'ussen/tussi', 'id' => 644)
                ));

        $filelib->setBackend($backend);

        $folder = $op->findByUrl($id);

        $this->assertInstanceOf('Xi\Filelib\Folder\FolderItem', $folder);

    }


    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findRootShouldFailWhenRootFolderIsNotFound()
    {

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findRootFolder')->will($this->returnValue(
                        false
                ));

        $filelib->setBackend($backend);

        $folder = $op->findRoot();

        $this->assertFalse($folder);
    }

    /**
     * @test
     */
    public function findRootShouldReturnFolderWhenRootFolderIsFound()
    {

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findRootFolder')->will($this->returnValue(
                        array('id' => 1, 'parent_id' => null)
                ));

        $filelib->setBackend($backend);

        $folder = $op->findRoot();

        $this->assertInstanceOf('Xi\Filelib\Folder\FolderItem', $folder);
    }




    public function provideDataForBuildRouteTest()
    {
        return array(
            array('lussutus/bansku/tohtori vesala/lamantiini/kaskas/losoboesk', 10),
            array('lussutus/bansku/tohtori vesala/lamantiini/kaskas', 9),
            array('lussutus/bansku/tohtori vesala', 4),
            array('lussutus/bansku/tohtori vesala/lamantiini/klaus kulju', 8),
            array('lussutus/bansku/tohtori vesala/lamantiini/puppe', 6),
        );
    }



    /**
     * @test
     * @dataProvider provideDataForBuildRouteTest
     */
    public function buildRouteShouldBuildBeautifulRoute($expected, $folderId)
    {
        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        // $op->expects($this->exactly(4))->method('buildRoute')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->any())->method('findFolder')
                ->will($this->returnCallback(function($folderId) {

                    $farr = array(
                        1 => array('parent_id' => null, 'name' => 'root'),
                        2 => array('parent_id' => 1, 'name' => 'lussutus'),
                        3 => array('parent_id' => 2, 'name' => 'bansku'),
                        4 => array('parent_id' => 3, 'name' => 'tohtori vesala'),
                        5 => array('parent_id' => 4, 'name' => 'lamantiini'),
                        6 => array('parent_id' => 5, 'name' => 'puppe'),
                        7 => array('parent_id' => 6, 'name' => 'nilkki'),
                        8 => array('parent_id' => 5, 'name' => 'klaus kulju'),
                        9 => array('parent_id' => 5, 'name' => 'kaskas'),
                        10 => array('parent_id' => 9, 'name' => 'losoboesk')
                    );

                    if (isset($farr[$folderId])) {
                        return $farr[$folderId];
                    }

                    return false;

                 }));

        $filelib->setBackend($backend);

        $folder = $op->find($folderId);

        $route = $op->buildRoute($folder);

        $this->assertEquals($expected, $route);


    }




   /**
    * @test
    */
    public function getFileOperatorShouldDelegateToFilelib()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $filelib->expects($this->once())->method('getFileOperator');

        $op = new DefaultFolderOperator($filelib);

        $op->getFileOperator();

    }


}