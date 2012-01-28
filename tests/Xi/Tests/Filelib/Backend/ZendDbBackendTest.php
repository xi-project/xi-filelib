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
    
    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function updateFileShouldThrowExceptionWithErroneousFile()
    {
        $updated = array(
            'id' => 1,
            'folder_id' => 666666,
            'mimetype' => 'image/jpg',
            'profile' => 'lussed',
            'size' => '1006',
            'name' => 'tohtori-sykero.png',
            'link' => 'tohtori-sykero.png',
            'date_uploaded' => new DateTime('2011-01-02 16:16:16'),
        );
        
        $file = FileItem::create($updated);
        
        $updated = $this->backend->updateFile($file);
    }
    
    
    /**
     * @test
     */
    public function deleteFileShouldDeleteFile()
    {
        $file = FileItem::create(array('id' => 5));
        
        $deleted = $this->backend->deleteFile($file);
        
        $this->assertTrue($deleted);
        
        $row = $this->backend->getDb()->fetchRow("SELECT * FROM xi_filelib_file WHERE id = 5");
        
        $this->assertFalse($row);
                
    }
    
    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function deleteFileShouldThrowExceptionWithErroneousFile()
    {
        $file = FileItem::create(array('id' => 'xooxoox'));
        
        $this->backend->deleteFile($file);
        
    }
    
    
    /**
     * @test
     */
    public function fileUploadShouldUploadFile()
    {
        $fidata = array(
            'mimetype' => 'image/png',
            'profile' => 'versioned',
            'size' => '1000',
            'name' => 'tohtori-tussi.png',
            'link' => 'tohtori-tussi.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
        );
        
        $fodata = array(
            'id' => 1,
            'parent_id' => null,
            'url' => '',
            'name' => '',
        );
        
        $file = FileItem::create($fidata);
        $folder = FolderItem::create($fodata);
        
        $file = $this->backend->upload($file, $folder); 
        
        $this->assertInstanceOf('\\Xi\\Filelib\\File\\File', $file);
        $this->assertInternalType('integer', $file->getId());
        
        $this->assertEquals($fodata['id'], $file->getFolderId());
        $this->assertEquals($fidata['mimetype'], $file->getMimeType());
        $this->assertEquals($fidata['profile'], $file->getProfile());

        $this->assertEquals($fidata['link'], $file->getLink());
        
        $this->assertEquals($fidata['date_uploaded'], $file->getDateUploaded());
        
    }
    
    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function fileUploadShouldThrowExceptionWithErroneousFolder()
    {        
        $fidata = array(
            'mimetype' => 'image/png',
            'profile' => 'versioned',
            'size' => '1000',
            'name' => 'tohtori-tussi.png',
            'link' => 'tohtori-tussi.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
        );
        
        $fodata = array(
            'id' => 666666,
            'parent_id' => null,
            'url' => '',
            'name' => '',
        );
        
        $file = FileItem::create($fidata);
        $folder = FolderItem::create($fodata);
        
        $file = $this->backend->upload($file, $folder); 

        
    }
    
    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function fileUploadShouldThrowExceptionWithAlreadyExistingFile()
    {        
        $fidata = array(
            'mimetype' => 'image/png',
            'profile' => 'versioned',
            'size' => '1000',
            'name' => 'tohtori-vesala.png',
            'link' => 'tohtori-vesala.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
        );
        
        $fodata = array(
            'id' => 1,
            'parent_id' => null,
            'url' => '',
            'name' => '',
        );
        
        $file = FileItem::create($fidata);
        $folder = FolderItem::create($fodata);
        
        $file = $this->backend->upload($file, $folder); 

        
    }
    
    
    /**
     * @test
     */
    public function findFileByFilenameShouldReturnCorrectFile()
    {
        $fidata = array(
            'mimetype' => 'image/png',
            'profile' => 'versioned',
            'size' => 1000,
            'name' => 'tohtori-vesala.png',
            'link' => 'tohtori-vesala.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
            'id' => 1,
            'folder_id' => 1,
        );
        
        $fodata = array(
            'id' => 1,
            'parent_id' => null,
            'url' => '',
            'name' => '',
        );
        
        $folder = FolderItem::create($fodata);    
        
        
        $file = $this->backend->findFileByFileName($folder, 'tohtori-vesala.png');
        
        $this->assertInternalType('array', $file);
                
        $this->assertEquals($fidata, $file);
        
        
    }
    
    /**
     * @test
     */
    public function findFileByFilenameShouldNotFindNonExistingFile()
    {
        $fidata = array(
            'mimetype' => 'image/png',
            'profile' => 'versioned',
            'size' => 1000,
            'name' => 'tohtori-vesala.png',
            'link' => 'tohtori-vesala.png',
            'date_uploaded' => '2011-01-01 16:16:16',
            'id' => 1,
            'folder_id' => 1,
        );
        
        $fodata = array(
            'id' => 1,
            'parent_id' => null,
            'url' => '',
            'name' => '',
        );
        
        $folder = FolderItem::create($fodata);    
        
        $file = $this->backend->findFileByFileName($folder, 'tohtori-tussi.png');
        
        $this->assertFalse($file);
        
    }
    
       
    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function findFileByFileNameShouldThrowExceptionWithErroneousFolder()
    {
        $fidata = array(
            'mimetype' => 'image/png',
            'profile' => 'versioned',
            'size' => 1000,
            'name' => 'tohtori-vesala.png',
            'link' => 'tohtori-vesala.png',
            'date_uploaded' => '2011-01-01 16:16:16',
            'id' => 1,
            'folder_id' => 1,
        );
        
        $fodata = array(
            'id' => 'shjisioshio',
            'parent_id' => null,
            'url' => '',
            'name' => '',
        );
        
        $folder = FolderItem::create($fodata);    
        
        $file = $this->backend->findFileByFileName($folder, 'tohtori-tussi.png');
        
        $this->assertFalse($file);
    
    }
    
    
    
    
    
}
