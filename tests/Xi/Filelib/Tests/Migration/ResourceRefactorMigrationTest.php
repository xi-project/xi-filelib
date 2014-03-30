<?php

namespace Xi\Filelib\Tests\Migration;

use Xi\Filelib\Migration\ResourceRefactorMigration;
use Xi\Filelib\Storage\FileIOException;

class ResourceRefactorMigrationTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Migration\ResourceRefactorMigration'));
        $this->assertContains('Xi\Filelib\Command\Command', class_implements('Xi\Filelib\Migration\ResourceRefactorMigration'));
    }

    public function provideData()
    {
        return array(
            array(false),
            array(true)
        );
    }

    /**
     * @test
     */
    public function topicIsCorrect()
    {
        $migration = new ResourceRefactorMigration();
        $this->assertEquals('xi_filelib.command.migration.resource_refactor', $migration->getTopic());
    }


    /**
     * @test
     * @dataProvider provideData
     */
    public function executeShouldDoMigration($expectFail)
    {
        $foop = $this->getMockedFolderRepository();
        $fiop = $this->getMockedFileRepository();
        $resource = $this->getMockedResource();
        $storage = $this->getMockedStorage();
        $backend = $this->getMockedBackend();
        $profile = $this->getMockedFileProfile();
        $pm = $this->getMockedProfileManager();

        $filelib = $this->getMockedFilelib(null, $fiop, $foop, $storage, null, $backend, null, null, $pm);

        $rootFolder = $this->getMockedFolder();
        $childFolder = $this->getMockedFolder();

        $rootFolder->expects($this->once())->method('setUuid')->with($this->isType('string'));
        $childFolder->expects($this->once())->method('setUuid')->with($this->isType('string'));

        if ($expectFail) {

            $storage->expects($this->once())->method('retrieve')
                ->with($resource)->will($this->throwException(new FileIOException('Game over man')));
            $resource->expects($this->never())->method('setHash');
            $backend->expects($this->never())->method('updateResource');

        } else {
            $storage->expects($this->once())->method('retrieve')
                ->with($resource)->will($this->returnValue(ROOT_TESTS . '/data/self-lussing-manatee.jpg'));

            $resource->expects($this->once())->method('setHash');
            $backend->expects($this->once())->method('updateResource')->with($resource);

        }

        $foop->expects($this->once())->method('findRoot')->will($this->returnValue($rootFolder));
        $foop->expects($this->exactly(2))->method('findSubFolders')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
             ->will($this->onConsecutiveCalls(array($childFolder), array()));
        $foop->expects($this->any())->method('generateUuid')->will($this->returnValue('uuid'));
        $foop->expects($this->exactly(2))->method('update')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'));

        $pm->expects($this->any())->method('getProfile')->with($this->isType('string'))
             ->will($this->returnValue($profile));

        $file = $this->getMockedFile();

        $fiop->expects($this->once())->method('findAll')
             ->will($this->returnValue(array($file)));

        $file->expects($this->any())->method('getProfile')->will($this->returnValue('lus'));
        $file->expects($this->once())->method('setUuid')->with($this->isType('string'));
        $file->expects($this->any())->method('getResource')->will($this->returnValue($resource));

        $profile->expects($this->any())->method('getFileVersions')->with($file)->will($this->returnValue(array('lus', 'xoo')));



        $migration = new ResourceRefactorMigration();

        $migration->attachTo($filelib);
        $migration->execute();
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $command = new ResourceRefactorMigration();

        $serialized = serialize($command);
        $command2 = unserialize($serialized);

        $this->assertInstanceOf('Xi\Filelib\Migration\ResourceRefactorMigration', $command2);
    }

}
