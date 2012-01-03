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
    
    
    
    
    public function setUp()
    {
        parent::setUp();
        
        $db = Zend_Db::factory('PDO_SQLITE', array(
            'dbname' => ROOT_TESTS . '/data/filelib-test.db',
        ));
        
        $this->backend = new ZendDbBackend();
        $this->backend->setDb($db);
        
        // $conn = $this->getConnection()->getConnection();
        
                
        // $conn->exec('DELETE FROM xi_filelib_folder');
       // $n->exec("DELETE FROM sqlite_sequence where name='xi_filelib_folder'");


        
        
                
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
        $lus = $this->backend->getFolderTable()->fetchAll();
        
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
    
        
        var_dump($ret);
        
    }
    
    
    
    
    
    
    
    
    
}
