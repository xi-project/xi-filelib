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
 */
class ZendDbBackendTest extends DbTestCase
{
    /**
     *
     * @var ZendDbBackend
     */
    protected $backend;
    
    
    protected static $conn;
    
    public static function setUpBeforeClass()
    {
        self::$conn = Zend_Db::factory('pdo_' . PDO_DRIVER, array(
            'host'     => PDO_HOST,
            'dbname'   => PDO_DBNAME,
            'username' => PDO_USERNAME,
            'password' => PDO_PASSWORD,
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
        parent::tearDown();
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

    
    /**
     * @test
     */
    public function deleteFolderShouldNotDeleteNonExistingFolder()
    {
        $data = array(
            'id' => 423789,
            'parent_id' => null,
            'name' => 'klus',
        );
        
        $folder = FolderItem::create($data);
        
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
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function findFileShouldThrowExceptionWithErroneousId()
    {
        $ret = $this->backend->findFile('xooxoeroe');
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
    
    /**
     * @test
     */
    public function updateFileShouldUpdateFile()
    {
        
        $data = array(
            'id' => 1,
            'folder_id' => 1,
            'mimetype' => 'image/png',
            'profile' => 'versioned',
            'size' => '1000',
            'name' => 'tohtori-vesala.png',
            'link' => 'tohtori-vesala.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
        );
        
        $updated = array(
            'id' => 1,
            'folder_id' => 2,
            'mimetype' => 'image/jpg',
            'profile' => 'lussed',
            'size' => '1006',
            'name' => 'tohtori-sykero.png',
            'link' => 'tohtori-sykero.png',
            'date_uploaded' => new DateTime('2011-01-02 16:16:16'),
        );
                
        $file = FileItem::create($updated);
        
        $updated = $this->backend->updateFile($file);
        
        $this->assertTrue($updated);
        
        
        $row = $this->backend->getDb()->fetchRow("SELECT * FROM xi_filelib_file WHERE id = 1");
                
        $this->assertEquals($row['id'], 1);
        $this->assertEquals($row['folder_id'], 2);
        $this->assertEquals($row['mimetype'], 'image/jpg');
        $this->assertEquals($row['fileprofile'], 'lussed');
        $this->assertEquals($row['filesize'], 1006);
        $this->assertEquals($row['filename'], 'tohtori-sykero.png');
        $this->assertEquals($row['filelink'], 'tohtori-sykero.png');
        $this->assertEquals($row['date_uploaded'], '2011-01-02 16:16:16');
       
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
