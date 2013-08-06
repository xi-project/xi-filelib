<?php

namespace Xi\Filelib\Tests\Migration;

use Xi\Filelib\FileLibrary;
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
        $this->assertContains('Xi\Filelib\Command', class_implements('Xi\Filelib\Migration\ResourceRefactorMigration'));
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
     * @dataProvider provideData
     */
    public function executeShouldDoMigration($expectFail)
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

        /*
        $retrieved = $this->filelib->getStorage()->retrieve($resource);
        $resource->setHash(sha1_file($retrieved));
        $resource->setVersions($profile->getFileVersions($file));
        $this->filelib->getBackend()->updateResource($resource);
        */

        $rootFolder = $this->getMockedFolder();
        $childFolder = $this->getMockedFolder();

        $rootFolder->expects($this->once())->method('setUuid')->with('uuid');
        $childFolder->expects($this->once())->method('setUuid')->with('uuid');

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



        $migration = new ResourceRefactorMigration($filelib);

        $filelib = $this->getMockedFilelib(null, $fiop, $foop, $storage);
        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        $migration->attachTo($filelib);
        $migration->execute();
    }




}
