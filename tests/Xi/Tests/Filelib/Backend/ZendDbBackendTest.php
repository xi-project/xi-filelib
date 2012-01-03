<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Backend\ZendDbBackend,
    \Zend_Db,
    Xi\Filelib\Folder\FolderItem
    ;

/**
 * Description of ZendDbTest
 *
 * @author pekkis
 */
class ZendDbBackendTest extends TestCase
{
    /**
     *
     * @var ZendDbBackend
     */
    protected $backend;
    
    
    protected static $conn;
    
    public static function setUpBeforeClass()
    {
        self::$conn = Zend_Db::factory('PDO_PGSQL', array(
            'host' => '127.0.0.1',
            'dbname' => 'filelib_test',
            'username' => 'pekkis',
            'password' => 'g04753m135'
        ));
        
    }
    
    
    
    public function setUp()
    {
        parent::setUp();
        
        
        $this->backend = new ZendDbBackend();
        $this->backend->setDb(self::$conn);
        
        // $conn = $this->getConnection()->getConnection();
        
                
        // $conn->exec('DELETE FROM xi_filelib_folder');
       // $n->exec("DELETE FROM sqlite_sequence where name='xi_filelib_folder'");
        
                
    }
    
    
    public function tearDown()
    {

        $this->backend = null;
    }
    
    
    
    
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
        
        $this->assertEquals(null, $folder['parent_id']);
        
        
    }
    

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
    public function zendDbGettersShouldReturnCorrectObjects()
    {
        $this->assertInstanceOf('Xi\Filelib\Backend\ZendDb\FileTable', $this->backend->getFileTable());
        $this->assertInstanceOf('Xi\Filelib\Backend\ZendDb\FolderTable', $this->backend->getFolderTable());
    }
    
    
    
    /**
     * @test
     * @dataProvider provideForFindFolder
     */
    public function findFolderShouldReturnCorrectFolder($folderId, $data)
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
     * @test
     */
    public function findFolderShouldReturnNullWhenTryingToFindNonExistingFolder()
    {
        $folder = $this->backend->findFolder(900);
        
        $this->assertEquals(false, $folder);
    }

    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function findFolderShouldThrowExceptionWhenTryingToFindErroneousFolder()
    {
        $folder = $this->backend->findFolder('xoo');

    }
    
    
    /**
     * @test
     */
    public function createFolderShouldCreateFolder()
    {
        $data = array(
            'parent_id' => 3,
            'name' => 'lusander',
            'url' => 'lussuttaja/tussin/lusander',
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
            'name' => 'lusander',
            'url' => 'lussuttaja/tussin/lusander',
        );
        
        $folder = FolderItem::create($data);
        
        $ret = $this->backend->createFolder($folder);
        
    }
    

    /**
     * @test
     */
    public function deleteFolderShouldDeleteFolder()
    {
        $data = array(
            'id' => 5,
            'parent_id' => null,
            'name' => 'klus',
        );
        
        $folder = FolderItem::create($data);
                
        $rows = $this->backend->getFolderTable()->fetchAll('id = 5');
        
        $this->assertEquals(1, $rows->count());
        
        $deleted = $this->backend->deleteFolder($folder);
        $this->assertTrue($deleted);
        
        
        $rows = $this->backend->getFolderTable()->fetchAll('id = 5');
        $this->assertEquals(0, $rows->count());
                
        $this->assertFalse($this->backend->findFolder(5));
        
    }

    
    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function deleteFolderShouldThrowExceptionWhenDeletingFolderWithFiles()
    {
        $data = array(
            'id' => 4,
            'parent_id' => null,
            'name' => 'klus',
        );
        
        $folder = FolderItem::create($data);
                
        $rows = $this->backend->getFolderTable()->fetchAll('id = 5');
        
        $this->assertEquals(1, $rows->count());
        
        $deleted = $this->backend->deleteFolder($folder);
        
    }

    
    
    public function deleteFolderShouldNotDeleteNonExistingFolder()
    {
        $data = array(
            'id' => 423789,
            'parent_id' => null,
            'name' => 'klus',
        );
        
        $deleted = $this->backend->deleteFolder($folder);
        $this->assertEquals(false, $deleted);

    }
    
    
    /**
     * @test
     */
    public function updateFolderShouldUpdateFolder()
    {
        $data = array(
            'id' => 3,
            'parent_id' => 2,
            'folderurl' => 'lussuttaja/tussin',
            'foldername' => 'tussin',
        );
        
        $row = $this->backend->getFolderTable()->fetchRow('id = 3')->toArray();
        
        $this->assertEquals($data, $row);
        
        
        $folder = FolderItem::create(array(
            'id' => 3,
            'parent_id' => 1,
            'url' => 'lussuttaja/lussander',
            'name' => 'lussander',
        ));

        $data = array(
            'id' => 3,
            'parent_id' => 1,
            'folderurl' => 'lussuttaja/lussander',
            'foldername' => 'lussander',
        );

        $ret = $this->backend->updateFolder($folder);
        $this->assertTrue($ret);
        
        $row = $this->backend->getFolderTable()->fetchRow('id = 3')->toArray();
        
        $this->assertEquals($data, $row);
        

    }
    
    
    /**
     * @test
     */
    public function updateFolderShouldNotUpdateNonExistingFolder()
    {
        $folder = FolderItem::create(array(
            'id' => 333,
            'parent_id' => 1,
            'url' => 'lussuttaja/lussander',
            'name' => 'lussander',
        ));
        
        $ret = $this->backend->updateFolder($folder);
        
        $this->assertFalse($ret);
        
    }
    
    
    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function updateFolderShouldThrowExceptionWhenUpdatingErroneousFolder()
    {
        $folder = FolderItem::create(array(
            'id' => 'xoofiili',
            'parent_id' => 'xoo',
            'url' => '',
            'name' => '',
        ));
        
        $ret = $this->backend->updateFolder($folder);
        
        $this->assertFalse($ret);
        
    }
    
    /**
     * @test
     */
    public function findSubFoldersShouldReturnArrayOfSubFolders()
    {
        $folder = FolderItem::create(array(
            'id' => 1,
            'parent_id' => null,
            'url' => '',
            'name' => '',
        ));
                
        $ret = $this->backend->findSubFolders($folder);
        
        $this->assertInternalType('array', $ret);
        $this->assertCount(1, $ret);
        
        $folder = FolderItem::create(array(
            'id' => 2,
            'parent_id' => null,
            'url' => '',
            'name' => '',
        ));
                
        $ret = $this->backend->findSubFolders($folder);
        
        $this->assertInternalType('array', $ret);
        $this->assertCount(3, $ret);
        

        $folder = FolderItem::create(array(
            'id' => 4,
            'parent_id' => null,
            'url' => '',
            'name' => '',
        ));
                
        $ret = $this->backend->findSubFolders($folder);
        
        $this->assertInternalType('array', $ret);
        $this->assertCount(0, $ret);
        
    }
    
    
    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function findSubFoldersShouldThrowExceptionForErroneousFolder()
    {
        $folder = FolderItem::create(array(
            'id' => 'xooxer',
            'parent_id' => null,
            'url' => '',
            'name' => '',
        ));
                
        $ret = $this->backend->findSubFolders($folder);
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
        $ret = $this->backend->findFolderByUrl('lussuttaja/tussinnnnn');
        
        $this->assertFalse($ret);
        
    }
    
    /**
     * @test
     */
    public function findFilesInShouldReturnArrayOfFiles()
    {
        $folder = FolderItem::create(array(
            'id' => 1,
            'parent_id' => null,
            'url' => '',
            'name' => '',
        ));
        
        $ret = $this->backend->findFilesIn($folder);
        
        $this->assertInternalType('array', $ret);
        
        $this->assertCount(1, $ret);
        

        $folder = FolderItem::create(array(
            'id' => 4,
            'parent_id' => null,
            'url' => '',
            'name' => '',
        ));
        
        $ret = $this->backend->findFilesIn($folder);
        
        $this->assertInternalType('array', $ret);
        
        $this->assertCount(2, $ret);
        
        
        $folder = FolderItem::create(array(
            'id' => 5,
            'parent_id' => null,
            'url' => '',
            'name' => '',
        ));
        
        $ret = $this->backend->findFilesIn($folder);
        
        $this->assertInternalType('array', $ret);
        
        $this->assertCount(0, $ret);

        
    }
    
    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function findFilesInShouldThrowExceptionWithErroneousFolder()
    {
        $folder = FolderItem::create(array(
            'id' => 'xoo',
            'parent_id' => null,
            'url' => '',
            'name' => '',
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
        
        $this->assertInstanceOf('\\DateTime', $ret['date_uploaded']);
        
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

            $this->assertInstanceOf('\\DateTime', $ret['date_uploaded']);
        }
        
    }
    
}
