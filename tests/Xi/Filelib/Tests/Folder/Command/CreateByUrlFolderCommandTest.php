<?php

namespace Xi\Filelib\Tests\Folder\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\Command\CreateByUrlFolderCommand;

class CreateByUrlFolderCommandTest extends \Xi\Filelib\Tests\TestCase
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
    public function createByUrlShouldCreateRecursivelyIfFolderDoesNotExist()
    {
        $op = $this->getMockedOp();

        $root = Folder::create(array('parent_id' => null, 'name' => 'root'));

        $op
            ->expects($this->once())
            ->method('findRoot')
            ->will($this->returnValue($root));

        $op
            ->expects($this->any())
            ->method('findByUrl')
            ->will($this->returnValue(false));

        $self = $this;
        $op
            ->expects($this->exactly(4))
            ->method('createCommand')
            ->with(
                'Xi\Filelib\Folder\Command\CreateFolderCommand',
                $this->isType('array')
            )
            ->will($this->returnCallback(function($className) use ($self) {
                $command = $self->getMockBuilder($className)->disableOriginalConstructor()->getMock();
                $command->expects($self->once())->method('execute');

                return $command;
            }));

        $command = new CreateByUrlFolderCommand('tussin/lussutus/festivaali/2012');
        $command->attachTo($this->getMockedFilelib(null, null, $op));
        $folder = $command->execute();

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
        $this->assertEquals('2012', $folder->getName());
    }

    /**
     * @test
     */
    public function createByUrlShouldCreateRecursivelyFromTheMiddleIfSomeFoldersExist()
    {
        $op = $this->getMockedOp();

        $root = Folder::create(array('parent_id' => null, 'name' => 'root'));

        $op
            ->expects($this->once())
            ->method('findRoot')
            ->will($this->returnValue($root));

        $self = $this;
        $op
            ->expects($this->exactly(2))
            ->method('createCommand')
            ->with(
                'Xi\Filelib\Folder\Command\CreateFolderCommand',
                $this->isType('array')
            )
            ->will($this->returnCallback(function($className) use ($self) {
            $command = $self->getMockBuilder($className)->disableOriginalConstructor()->getMock();
            $command->expects($self->once())->method('execute');

            return $command;
        }));

        $op->expects($this->exactly(5))->method('findByUrl')->will($this->returnCallback(function($url) {

            if ($url === 'tussin') {
                return Folder::create(array('id' => 536, 'parent_id' => 545, 'url' => 'tussin'));
            }

            if ($url === 'tussin/lussutus') {
                return Folder::create(array('id' => 537, 'parent_id' => 5476, 'url' => 'tussin/lussutus'));
            }

            return false;

        }));

        $command = new CreateByUrlFolderCommand('tussin/lussutus/festivaali/2012');
        $command->attachTo($this->getMockedFilelib(null, null, $op));
        $folder = $command->execute();

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
        $this->assertEquals('2012', $folder->getName());

    }

        /**
     * @test
     */
    public function createByUrlShouldExitEarlyIfFolderExists()
    {
        $op = $this
            ->getMockBuilder('Xi\Filelib\Folder\FolderOperator')
            ->disableOriginalConstructor()
            ->getMock();

        $root = Folder::create(array('parent_id' => null, 'name' => 'root'));

        $op
            ->expects($this->never())
            ->method('findRoot');

        $op
            ->expects($this->once())
            ->method('findByUrl')
            ->with('tussin/lussutus/festivaali/2010')
            ->will($this->returnValue(Folder::create(array('id' => 666))));

        $command = new CreateByUrlFolderCommand('tussin/lussutus/festivaali/2010');
        $command->attachTo($this->getMockedFilelib(null, null, $op));
        $folder = $command->execute();

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
        $this->assertEquals(666, $folder->getId());

    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedOp()
    {
        $op = $this
            ->getMockBuilder('Xi\Filelib\Folder\FolderOperator')
            ->disableOriginalConstructor()
            ->setMethods(array('findRoot', 'findByUrl', 'createCommand'))
            ->getMock();

        return $op;
    }

    /**
     * @test
     */
    public function returnsProperMessage()
    {
        $url = 'tenhunen/imaisee/mehevan/keihaan';
        $command = new CreateByUrlFolderCommand($url);

        $message = $command->getMessage();

        $this->assertInstanceOf('Pekkis\Queue\Message', $message);
        $this->assertSame('xi_filelib.command.folder.create_by_url', $message->getType());
        $this->assertEquals(
            array(
                'url' => $url,
            ),
            $message->getData()
        );
    }
}
