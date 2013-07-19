<?php

namespace Xi\Filelib\Tests\Migration;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Migration\ResourceRefactorMigration;

class ResourceRefactorMigrationTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Migration\ResourceRefactorMigration'));
        $this->assertContains('Xi\Filelib\Command', class_implements('Xi\Filelib\Migration\ResourceRefactorMigration'));
    }

    /**
     * @test
     */
    public function executeShouldMigrate()
    {
        $foop = $this->getMockedFolderOperator();
        $fiop = $this->getMockedFileOperator();
        $resource = $this->getMockedResource();
        $storage = $this->getMockedStorage();
        $backend = $this->getMockedBackend();
        $profile = $this->getMockedFileProfile();
        $filelib = $this->getMockedFilelib(null, $fiop, $foop);
        $filelib->expects($this->any())->method('getStorage')->will($this->returnValue($storage));
        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        $rootFolder = $this->getMockedFolder();
        $childFolder = $this->getMockedFolder();

        $rootFolder->expects($this->once())->method('setUuid')->with('uuid');
        $childFolder->expects($this->once())->method('setUuid')->with('uuid');

        $storage->expects($this->once())->method('retrieve')
                ->with($resource)->will($this->returnValue(ROOT_TESTS . '/data/self-lussing-manatee.jpg'));

        $foop->expects($this->once())->method('findRoot')->will($this->returnValue($rootFolder));
        $foop->expects($this->exactly(2))->method('findSubFolders')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
             ->will($this->onConsecutiveCalls(array($childFolder), array()));
        $foop->expects($this->any())->method('generateUuid')->will($this->returnValue('uuid'));
        $foop->expects($this->exactly(2))->method('update')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'));

        $fiop->expects($this->any())->method('generateUuid')->will($this->returnValue('uuid'));
        $fiop->expects($this->any())->method('getProfile')->with($this->isType('string'))
             ->will($this->returnValue($profile));

        $file = $this->getMock('Xi\Filelib\File\File');

        $fiop->expects($this->once())->method('findAll')
             ->will($this->returnValue(array($file)));

        $file->expects($this->any())->method('getProfile')->will($this->returnValue('lus'));
        $file->expects($this->once())->method('setUuid')->with('uuid');
        $file->expects($this->any())->method('getResource')->will($this->returnValue($resource));

        $profile->expects($this->any())->method('getFileVersions')->with($file)->will($this->returnValue(array('lus', 'xoo')));

        $backend->expects($this->once())->method('updateResource')->with($resource);

        $migration = new ResourceRefactorMigration($filelib);
        $migration->execute();
    }

}
