<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Folder\FolderItem;

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

        $this->assertCount(
            1,
            $this->conn->fetchAll("SELECT * FROM xi_filelib_folder WHERE id = 5")
        );

        $this->assertTrue($this->backend->deleteFolder($folder));

        $this->assertCount(
            0,
            $this->conn->fetchAll('SELECT * FROM xi_filelib_folder WHERE id = 5')
        );

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

        $this->assertCount(
            1,
            $this->conn->fetchAll('SELECT * FROM xi_filelib_folder WHERE id = 5')
        );

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
}
