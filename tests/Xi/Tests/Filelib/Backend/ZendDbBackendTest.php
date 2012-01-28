<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Backend\ZendDbBackend,
    \Zend_Db,
    Xi\Filelib\Folder\FolderItem,
    Xi\Filelib\File\FileItem,
    \DateTime
    ;

/**
 * Description of ZendDbTest
 *
 * @author pekkis
 * @group  zenddb
 */
class ZendDbBackendTest extends RelationalDbTestCase
{
    /**
     *
     * @var ZendDbBackend
     */
    protected $backend;
    
    
    protected $conn;

    public function setUp()
    {
        parent::setUp();

        $this->conn = Zend_Db::factory('pdo_' . PDO_DRIVER, array(
            'host'     => PDO_HOST,
            'dbname'   => PDO_DBNAME,
            'username' => PDO_USERNAME,
            'password' => PDO_PASSWORD,
        ));

        $this->backend = new ZendDbBackend();
        $this->backend->setDb($this->conn);
        
        // $conn = $this->getConnection()->getConnection();
        
                
        // $conn->exec('DELETE FROM xi_filelib_folder');
       // $n->exec("DELETE FROM sqlite_sequence where name='xi_filelib_folder'");
        
                
    }
    
    /**
     * @test
     */
    public function zendDbGettersShouldReturnCorrectObjects()
    {
        $this->assertInstanceOf('Xi\Filelib\Backend\ZendDb\FileTable', $this->backend->getFileTable());
        $this->assertInstanceOf('Xi\Filelib\Backend\ZendDb\FolderTable', $this->backend->getFolderTable());
    }
}
