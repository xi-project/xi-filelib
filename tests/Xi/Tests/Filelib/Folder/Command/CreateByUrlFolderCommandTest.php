<?php

namespace Xi\Tests\Filelib\Folder\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\Command\CreateByUrlFolderCommand;

class CreateByUrlFolderCommandTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\Command\CreateByUrlFolderCommand'));
        $this->assertContains('Xi\Filelib\Folder\Command\FolderCommand', class_implements('Xi\Filelib\Folder\Command\CreateByUrlFolderCommand'));
    }


    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this->getMockBuilder('Xi\Filelib\Folder\FolderOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array('createCommand'))
                    ->getMock();

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

        $url = 'tussen/hofen/meister';

        $command = new CreateByUrlFolderCommand($op, $url);

        $serialized = serialize($command);
        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'folderOperator', $command2);
        $this->assertAttributeEquals($url, 'url', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);

    }



    /**
     * @test
     */
    public function createByUrlShouldCreateRecursivelyIfFolderDoesNotExist()
    {
        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Platform\Platform');
        $backend->expects($this->once())->method('findRootFolder')->will($this->returnValue(array('id' => 1, 'name' => 'root')));


        $backend->expects($this->exactly(4))->method('createFolder')->will($this->returnCallback(function($folder) {
            static $count = 1;
            $folder->setId($count++);
            return $folder;
        }));


        $backend->expects($this->exactly(5))->method('findFolderByUrl')->will($this->returnValue(false));

        $filelib->setBackend($backend);

        $command = new CreateByUrlFolderCommand($op, 'tussin/lussutus/festivaali/2012');
        $folder = $command->execute();

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
        $this->assertEquals('2012', $folder->getName());

    }


    /**
     * @test
     */
    public function createByUrlShouldCreateRecursivelyFromTheMiddleIfSomeFoldersExist()
    {
        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Platform\Platform');
        $backend->expects($this->once())->method('findRootFolder')->will($this->returnValue(array('id' => 1, 'name' => 'root')));


        $backend->expects($this->exactly(2))->method('createFolder')->will($this->returnCallback(function($folder) {
            static $count = 1;
            $folder->setId($count++);
            return $folder;
        }));

        $backend->expects($this->exactly(5))->method('findFolderByUrl')->will($this->returnCallback(function($url) {

            if ($url === 'tussin') {
                return array('id' => 536, 'parent_id' => 545, 'url' => 'tussin');
            }

            if ($url === 'tussin/lussutus') {
                return array('id' => 537, 'parent_id' => 5476, 'url' => 'tussin/lussutus');
            }

            return false;

        }));

        $filelib->setBackend($backend);


        $command = new CreateByUrlFolderCommand($op, 'tussin/lussutus/festivaali/2012');
        $folder = $command->execute();

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
        $this->assertEquals('2012', $folder->getName());

    }


        /**
     * @test
     */
    public function createByUrlShouldExitEarlyIfFolderExists()
    {
        $filelib = new FileLibrary();
        $op = new FolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Platform\Platform');

        $backend->expects($this->never())->method('findRoot');

        $backend->expects($this->once())->method('findFolderByUrl')->with($this->equalTo('tussin/lussutus/festivaali/2010'))
                ->will($this->returnValue(array('id' => 666, 'parent_id' => 555)));


        $filelib->setBackend($backend);

        $command = new CreateByUrlFolderCommand($op, 'tussin/lussutus/festivaali/2010');
        $folder = $command->execute();

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
        $this->assertEquals(666, $folder->getId());

    }







}

