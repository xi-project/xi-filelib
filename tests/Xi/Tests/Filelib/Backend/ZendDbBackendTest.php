<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Backend\ZendDbBackend,
    \Zend_Db;

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
    
    
    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getDataSet()
    {
        return new ArrayDataSet(array(
            'xi_filelib_folder' => array(

                array(
                    'id' => 1,
                    'parent_id' => null,
                    'url' => '',
                    'name' => 'root',
                ),
                
                array(
                    'id' => 2,
                    'parent_id' => 1,
                    'url' => 'lussuttaja',
                    'name' => 'lussuttaja',
                ),
                
                array(
                    'id' => 3,
                    'parent_id' => 2,
                    'url' => 'lussuttaja/tussin',
                    'name' => 'tussin',
                ),

                array(
                    'id' => 4,
                    'parent_id' => 2,
                    'url' => 'lussuttaja/banskun',
                    'name' => 'banskun',
                ),
                
                
            ),
        ));
    }
    
    
    public function setUp()
    {
        $db = Zend_Db::factory('PDO_SQLITE', array(
            'dbname' => ROOT_TESTS . '/data/filelib-test.db',
        ));
        
        $this->backend = new ZendDbBackend();
        $this->backend->setDb($db);
        
        $conn = $this->getConnection()->getConnection();
        
                
        // $conn->exec('DELETE FROM xi_filelib_folder');
        $conn->exec("DELETE FROM sqlite_sequence where name='xi_filelib_folder'");


        
        
                
    }
    
    
    /**
     * @test
     */
    public function findRootFolderShouldReturnRootFolder()
    {
    
        var_dump($this->backend->getFolderTable()->fetchAll()->toArray());
        
        
        $folder = $this->backend->findRootFolder();
        
        var_dump($folder);
        
        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('parent_id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('url', $folder);
        
        $this->assertEquals(null, $folder['parent_id']);
        
        
    }
    

    public function provideForFindFolder()
    {
        return array(
            array(1, array(array('name' => 'root'))),
            array(2, array(array('name' => 'lussuttaja'))),
            array(3, array(array('name' => 'tussin'))),
            array(4, array(array('name' => 'banskun'))),
        );
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
        
        $this->assertEquals($folderId, $data['id']);
        
    }
    
    
    
    
    
    
    
}
