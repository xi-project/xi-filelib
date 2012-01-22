<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Backend\Doctrine2Backend,
    Xi\Filelib\Folder\FolderItem,
    Xi\Filelib\File\FileItem,
    DateTime,
    Exception,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration,
    Doctrine\DBAL\Connection,
    Doctrine\Common\Cache\ArrayCache,
    PHPUnit_Framework_MockObject_MockObject;

/**
 * @group doctrine
 */
class Doctrine2BackendTest extends DbTestCase
{
    /**
     * @var Doctrine2Backend
     */
    protected $backend;

    /**
     * @var Connection
     */
    protected $conn;

    /**
     * @var EntityManager
     */
    protected $em;

    public function setUp()
    {
        parent::setUp();

        $cache = new ArrayCache();

        $config = new Configuration();
        $config->setMetadataCacheImpl($cache);
        $config->setMetadataDriverImpl(
            $config->newDefaultAnnotationDriver(
                ROOT_TESTS . '/../library/Xi/Filelib/Backend/Doctrine2/Entity'
            )
        );
        $config->setQueryCacheImpl($cache);
        $config->setProxyDir(ROOT_TESTS . '/data/temp');
        $config->setProxyNamespace('FilelibTest\Proxies');
        $config->setAutoGenerateProxyClasses(true);

        $connectionOptions = array(
            'driver'   => 'pdo_' . PDO_DRIVER,
            'dbname'   => PDO_DBNAME,
            'user'     => PDO_USERNAME,
            'password' => PDO_PASSWORD,
            'host'     => PDO_HOST,
        );

        $em = EntityManager::create($connectionOptions, $config);

        $this->em   = $em;
        $this->conn = $em->getConnection();

        $this->backend = new Doctrine2Backend();
        $this->backend->setEntityManager($em);
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

        $this->assertNull($folder['parent_id']);
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
    public function entityClassGettersShouldReturnCorrectClassNames()
    {
        $this->assertEquals('Xi\Filelib\Backend\Doctrine2\Entity\File',
                            $this->backend->getFileEntityName());

        $this->assertEquals('Xi\Filelib\Backend\Doctrine2\Entity\Folder',
                            $this->backend->getFolderEntityName());
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

        $ret = $this->backend->createFolder(FolderItem::create($data));
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

    /**
     * @test
     */
    public function updateFolderShouldUpdateFolder()
    {
        $data = array(
            'id'         => 3,
            'parent_id'  => 2,
            'folderurl'  => 'lussuttaja/tussin',
            'foldername' => 'tussin',
        );

        $this->assertEquals(
            $data,
            $this->conn->fetchAssoc('SELECT * FROM xi_filelib_folder WHERE id = 3')
        );

        $folder = FolderItem::create(array(
            'id'        => 3,
            'parent_id' => 1,
            'url'       => 'lussuttaja/lussander',
            'name'      => 'lussander',
        ));

        $data = array(
            'id'         => 3,
            'parent_id'  => 1,
            'folderurl'  => 'lussuttaja/lussander',
            'foldername' => 'lussander',
        );

        $this->assertTrue($this->backend->updateFolder($folder));

        $this->assertEquals(
            $data,
            $this->conn->fetchAssoc('SELECT * FROM xi_filelib_folder WHERE id = 3')
        );
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
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findFileRethrowsException()
    {
        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
           ->method('find')
           ->will($this->throwException(new Exception()));

        $this->backend->setEntityManager($em);
        $this->backend->findFile(1);
    }

    /**
     * @test
     */
    public function findFileReturnsFalseIfFileIsNotFound()
    {
        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
           ->method('find')
           ->will($this->returnValue(null));

        $this->backend->setEntityManager($em);

        $this->assertFalse($this->backend->findFile(1));
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
        $updated = array(
            'id'            => 1,
            'folder_id'     => 2,
            'mimetype'      => 'image/jpg',
            'profile'       => 'lussed',
            'size'          => '1006',
            'name'          => 'tohtori-sykero.png',
            'link'          => 'tohtori-sykero.png',
            'date_uploaded' => new DateTime('2011-01-02 16:16:16'),
        );

        $file = FileItem::create($updated);

        $this->assertTrue($this->backend->updateFile($file));

        $row = $this->conn->fetchAssoc("SELECT * FROM xi_filelib_file WHERE id = 1");

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
     * @expectedException Xi\Filelib\FilelibException
     */
    public function updateFileShouldThrowExceptionWithErroneousFile()
    {
        $updated = array(
            'id'            => 1,
            'folder_id'     => 666666,
            'mimetype'      => 'image/jpg',
            'profile'       => 'lussed',
            'size'          => '1006',
            'name'          => 'tohtori-sykero.png',
            'link'          => 'tohtori-sykero.png',
            'date_uploaded' => new DateTime('2011-01-02 16:16:16'),
        );

        $file = FileItem::create($updated);

        $this->backend->updateFile($file);
    }

    /**
     * @test
     */
    public function deleteFileShouldDeleteFile()
    {
        $file = FileItem::create(array('id' => 5));

        $this->assertTrue($this->backend->deleteFile($file));

        $this->assertFalse(
            $this->conn->fetchAssoc("SELECT * FROM xi_filelib_file WHERE id = 5")
        );
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
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
            'mimetype'      => 'image/png',
            'profile'       => 'versioned',
            'size'          => '1000',
            'name'          => 'tohtori-tussi.png',
            'link'          => 'tohtori-tussi.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
        );

        $fodata = array(
            'id'        => 1,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        );

        $file = FileItem::create($fidata);
        $folder = FolderItem::create($fodata);

        $file = $this->backend->upload($file, $folder);

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);
        $this->assertInternalType('integer', $file->getId());

        $this->assertEquals($fodata['id'], $file->getFolderId());
        $this->assertEquals($fidata['mimetype'], $file->getMimeType());
        $this->assertEquals($fidata['profile'], $file->getProfile());
        $this->assertEquals($fidata['link'], $file->getLink());
        $this->assertEquals($fidata['date_uploaded'], $file->getDateUploaded());
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function fileUploadShouldThrowExceptionWithErroneousFolder()
    {
        $fidata = array(
            'mimetype'      => 'image/png',
            'profile'       => 'versioned',
            'size'          => '1000',
            'name'          => 'tohtori-tussi.png',
            'link'          => 'tohtori-tussi.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
        );

        $fodata = array(
            'id'        => 666666,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        );

        $file = FileItem::create($fidata);
        $folder = FolderItem::create($fodata);

        $this->backend->upload($file, $folder);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function fileUploadShouldThrowExceptionWithAlreadyExistingFile()
    {
        $fidata = array(
            'mimetype'      => 'image/png',
            'profile'       => 'versioned',
            'size'          => '1000',
            'name'          => 'tohtori-vesala.png',
            'link'          => 'tohtori-vesala.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
        );

        $fodata = array(
            'id'        => 1,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        );

        $file = FileItem::create($fidata);
        $folder = FolderItem::create($fodata);

        $this->backend->upload($file, $folder);
    }

    /**
     * @test
     */
    public function findFileByFilenameShouldReturnCorrectFile()
    {
        $fidata = array(
            'mimetype'      => 'image/png',
            'profile'       => 'versioned',
            'size'          => 1000,
            'name'          => 'tohtori-vesala.png',
            'link'          => 'tohtori-vesala.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
            'id'            => 1,
            'folder_id'     => 1,
        );

        $fodata = array(
            'id'        => 1,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
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
            'mimetype'      => 'image/png',
            'profile'       => 'versioned',
            'size'          => 1000,
            'name'          => 'tohtori-vesala.png',
            'link'          => 'tohtori-vesala.png',
            'date_uploaded' => '2011-01-01 16:16:16',
            'id'            => 1,
            'folder_id'     => 1,
        );

        $fodata = array(
            'id'        => 1,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        );

        $folder = FolderItem::create($fodata);

        $this->assertFalse(
            $this->backend->findFileByFileName($folder, 'tohtori-tussi.png')
        );
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findFileByFileNameShouldThrowExceptionWithErroneousFolder()
    {
        $folder = FolderItem::create(array(
            'id'        => 'shjisioshio',
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $this->assertFalse(
            $this->backend->findFileByFileName($folder, 'tohtori-tussi.png')
        );
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findFileByFileNameRethrowsException()
    {
        $folder = FolderItem::create(array(
            'id'        => 1,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
           ->method('createQueryBuilder')
           ->will($this->throwException(new Exception()));

        $this->backend->setEntityManager($em);
        $this->backend->findFileByFileName($folder, 'xoo.png');
    }

    /**
     * @test
     */
    public function getsAndSetsFileEntityName()
    {
        $fileEntityName = 'Foo\Bar';

        $this->assertNotEquals($fileEntityName,
                               $this->backend->getFileEntityName());

        $this->backend->setFileEntityName($fileEntityName);

        $this->assertEquals($fileEntityName,
                            $this->backend->getFileEntityName());
    }

    /**
     * @test
     */
    public function getsAndSetsFolderEntityName()
    {
        $folderEntityName = 'Xoo\Bar';

        $this->assertNotEquals($folderEntityName,
                               $this->backend->getFolderEntityName());

        $this->backend->setFolderEntityName($folderEntityName);

        $this->assertEquals($folderEntityName,
                            $this->backend->getFolderEntityName());
    }

    /**
     * @test
     */
    public function getsAndsetsEntityManager()
    {
        $em = $this->createEntityManagerMock();

        $this->assertNotSame($em, $this->backend->getEntityManager());

        $this->backend->setEntityManager($em);

        $this->assertSame($em, $this->backend->getEntityManager());
    }

    /**
     * @test
     */
    public function getsAndSetsFilelib()
    {
        $filelib = $this->getMockBuilder('Xi\Filelib\FileLibrary')
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->assertNotSame($filelib, $this->backend->getFilelib());

        $this->backend->setFilelib($filelib);

        $this->assertSame($filelib, $this->backend->getFilelib());
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function createEntityManagerMock()
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
