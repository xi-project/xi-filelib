<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\File\FileItem,
    Xi\Filelib\Folder\FolderItem,
    DateTime;

/**
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 */
abstract class RelationalDbTestCase extends DbTestCase
{
    /**
     * @test
     */
    public function findRootFolderShouldReturnRootFolder()
    {
        $folder = $this->backend->findRootFolder();

        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('parent_id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('url', $folder);

        $this->assertNull($folder['parent_id']);
    }

    /**
     * @test
     * @dataProvider provideForFindFolder
     * @param integer $folderId
     * @param array   $data
     */
    public function findFolderShouldReturnCorrectFolder($folderId, array $data)
    {
        $folder = $this->backend->findFolder($folderId);

        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('parent_id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('url', $folder);

        $this->assertEquals($folderId, $folder['id']);
        $this->assertEquals($data['name'], $folder['name']);
    }

    /**
     * @return array
     */
    public function provideForFindFolder()
    {
        return array(
            array(1, array('name' => 'root')),
            array(2, array('name' => 'lussuttaja')),
            array(3, array('name' => 'tussin')),
            array(4, array('name' => 'banskun')),
        );
    }

    /**
     * @test
     */
    public function findFolderShouldReturnNullWhenTryingToFindNonExistingFolder()
    {
        $this->assertFalse($this->backend->findFolder(900));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findFolderShouldThrowExceptionWhenTryingToFindErroneousFolder()
    {
        $this->backend->findFolder('xoo');
    }

    /**
     * @test
     */
    public function createFolderShouldCreateFolder()
    {
        $data = array(
            'parent_id' => 3,
            'name'      => 'lusander',
            'url'       => 'lussuttaja/tussin/lusander',
        );

        $folder = FolderItem::create($data);

        $this->assertNull($folder->getId());

        $ret = $this->backend->createFolder($folder);

        $this->assertInternalType('integer', $ret->getId());
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function createFolderShouldThrowExceptionWhenFolderIsInvalid()
    {
        $data = array(
            'parent_id' => 666,
            'name'      => 'lusander',
            'url'       => 'lussuttaja/tussin/lusander',
        );

        $this->backend->createFolder(FolderItem::create($data));
    }

    /**
     * @test
     */
    public function deleteFolderShouldDeleteFolder()
    {
        $data = array(
            'id'        => 5,
            'parent_id' => null,
            'name'      => 'klus',
        );

        $folder = FolderItem::create($data);

        $this->assertInternalType('array', $this->backend->findFolder(5));

        $this->assertTrue($this->backend->deleteFolder($folder));

        $this->assertFalse($this->backend->findFolder(5));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function deleteFolderShouldThrowExceptionWhenDeletingFolderWithFiles()
    {
        $data = array(
            'id'        => 4,
            'parent_id' => null,
            'name'      => 'klus',
        );

        $folder = FolderItem::create($data);

        $this->assertInternalType('array', $this->backend->findFolder(5));

        $this->backend->deleteFolder($folder);
    }

    /**
     * @test
     */
    public function deleteFolderShouldNotDeleteNonExistingFolder()
    {
        $data = array(
            'id'        => 423789,
            'parent_id' => null,
            'name'      => 'klus',
        );

        $folder = FolderItem::create($data);

        $this->assertFalse($this->backend->deleteFolder($folder));
    }

    /**
     * @test
     */
    public function updateFolderShouldUpdateFolder()
    {
        $data = array(
            'id'        => 3,
            'parent_id' => 2,
            'url'       => 'lussuttaja/tussin',
            'name'      => 'tussin',
        );

        $this->assertEquals($data, $this->backend->findFolder(3));

        $updateData = array(
            'id'        => 3,
            'parent_id' => 1,
            'url'       => 'lussuttaja/lussander',
            'name'      => 'lussander',
        );

        $folder = FolderItem::create($updateData);

        $this->assertTrue($this->backend->updateFolder($folder));
        $this->assertEquals($updateData, $this->backend->findFolder(3));
    }

    /**
     * @test
     */
    public function updatesRootFolder()
    {
        $data = array(
            'id'        => 3,
            'parent_id' => null,
            'url'       => 'foo/bar',
            'name'      => 'xoo',
        );

        $folder = FolderItem::create($data);

        $this->assertTrue($this->backend->updateFolder($folder));
        $this->assertEquals($data, $this->backend->findFolder(3));
    }

    /**
     * @test
     */
    public function updateFolderShouldNotUpdateNonExistingFolder()
    {
        $folder = FolderItem::create(array(
            'id'        => 333,
            'parent_id' => 1,
            'url'       => 'lussuttaja/lussander',
            'name'      => 'lussander',
        ));

        $this->assertFalse($this->backend->updateFolder($folder));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function updateFolderShouldThrowExceptionWhenUpdatingErroneousFolder()
    {
        $folder = FolderItem::create(array(
            'id'        => 'xoofiili',
            'parent_id' => 'xoo',
            'url'       => '',
            'name'      => '',
        ));

        $this->assertFalse($this->backend->updateFolder($folder));
    }

    /**
     * @test
     */
    public function findSubFoldersShouldReturnArrayOfSubFolders()
    {
        $folder = FolderItem::create(array(
            'id'        => 1,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findSubFolders($folder);

        $this->assertInternalType('array', $ret);
        $this->assertCount(1, $ret);

        $folder = FolderItem::create(array(
            'id'        => 2,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findSubFolders($folder);

        $this->assertInternalType('array', $ret);
        $this->assertCount(3, $ret);

        $folder = FolderItem::create(array(
            'id'        => 4,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findSubFolders($folder);

        $this->assertInternalType('array', $ret);
        $this->assertCount(0, $ret);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findSubFoldersShouldThrowExceptionForErroneousFolder()
    {
        $folder = FolderItem::create(array(
            'id'        => 'xooxer',
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $this->backend->findSubFolders($folder);
    }

    /**
     * @test
     */
    public function findFolderByUrlShouldReturnFolder()
    {
        $ret = $this->backend->findFolderByUrl('lussuttaja/tussin');

        $this->assertInternalType('array', $ret);
        $this->assertEquals(3, $ret['id']);
    }

    /**
     * @test
     */
    public function findFolderByUrlShouldNotReturnNonExistingFolder()
    {
        $this->assertFalse(
            $this->backend->findFolderByUrl('lussuttaja/tussinnnnn')
        );
    }

    /**
     * @test
     */
    public function findFilesInShouldReturnArrayOfFiles()
    {
        $folder = FolderItem::create(array(
            'id'        => 1,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findFilesIn($folder);

        $this->assertInternalType('array', $ret);
        $this->assertCount(1, $ret);

        $folder = FolderItem::create(array(
            'id'        => 4,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findFilesIn($folder);

        $this->assertInternalType('array', $ret);
        $this->assertCount(2, $ret);

        $folder = FolderItem::create(array(
            'id'        => 5,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findFilesIn($folder);

        $this->assertInternalType('array', $ret);
        $this->assertCount(0, $ret);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findFilesInShouldThrowExceptionWithErroneousFolder()
    {
        $folder = FolderItem::create(array(
            'id'        => 'xoo',
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findFilesIn($folder);
    }

    /**
     * @test
     */
    public function findFileShouldReturnFile()
    {
        $ret = $this->backend->findFile(1);

        $this->assertInternalType('array', $ret);

        $this->assertArrayHasKey('id', $ret);
        $this->assertArrayHasKey('folder_id', $ret);
        $this->assertArrayHasKey('mimetype', $ret);
        $this->assertArrayHasKey('profile', $ret);
        $this->assertArrayHasKey('size', $ret);
        $this->assertArrayHasKey('name', $ret);
        $this->assertArrayHasKey('link', $ret);
        $this->assertArrayHasKey('date_uploaded', $ret);

        $this->assertInstanceOf('DateTime', $ret['date_uploaded']);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findFileShouldThrowExceptionWithErroneousId()
    {
        $this->backend->findFile('xooxoeroe');
    }

    /**
     * @test
     */
    public function findAllFilesShouldReturnAllFiles()
    {
        $rets = $this->backend->findAllFiles();

        $this->assertInternalType('array', $rets);
        $this->assertCount(5, $rets);

        foreach ($rets as $ret) {
            $this->assertInternalType('array', $ret);

            $this->assertArrayHasKey('id', $ret);
            $this->assertArrayHasKey('folder_id', $ret);
            $this->assertArrayHasKey('mimetype', $ret);
            $this->assertArrayHasKey('profile', $ret);
            $this->assertArrayHasKey('size', $ret);
            $this->assertArrayHasKey('name', $ret);
            $this->assertArrayHasKey('link', $ret);
            $this->assertArrayHasKey('date_uploaded', $ret);

            $this->assertInstanceOf('DateTime', $ret['date_uploaded']);
        }
    }

    /**
     * @test
     */
    public function updateFileShouldUpdateFile()
    {
        $data = array(
            'id'            => 1,
            'folder_id'     => 2,
            'mimetype'      => 'image/jpg',
            'profile'       => 'lussed',
            'size'          => '1006',
            'name'          => 'tohtori-sykero.png',
            'link'          => 'tohtori-sykero.png',
            'date_uploaded' => new DateTime('2011-01-02 16:16:16'),
        );

        $file = FileItem::create($data);

        $this->assertTrue($this->backend->updateFile($file));
        $this->assertEquals($data, $this->backend->findFile(1));
    }
}
