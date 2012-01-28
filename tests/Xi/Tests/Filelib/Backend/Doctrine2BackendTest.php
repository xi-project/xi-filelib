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
    Doctrine\ORM\NoResultException,
    Doctrine\ORM\EntityNotFoundException,
    Doctrine\Common\Cache\ArrayCache,
    PHPUnit_Framework_MockObject_MockObject;

/**
 * @group doctrine
 */
class Doctrine2BackendTest extends RelationalDbTestCase
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
    public function findRootFolderCreatesRootFolderIfItDoesNotExist()
    {
        $query = $this->createQueryMock();
        $query->expects($this->once())
              ->method('getSingleResult')
              ->will($this->throwException(new NoResultException()));

        $qb = $this->createQueryBuilderMock();
        $qb->expects($this->once())
           ->method('getQuery')
           ->will($this->returnValue($query));

        $qb->expects($this->once())
           ->method('select')
           ->will($this->returnSelf());

        $qb->expects($this->once())
           ->method('from')
           ->will($this->returnSelf());

        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
           ->method('createQueryBuilder')
           ->will($this->returnValue($qb));

        $this->backend->setEntityManager($em);

        $folder = $this->backend->findRootFolder();

        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('parent_id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('url', $folder);
        $this->assertNull($folder['parent_id']);
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
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findFolderRethrowsException()
    {
        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
           ->method('find')
           ->will($this->throwException(new Exception()));

        $this->backend->setEntityManager($em);
        $this->backend->findFolder(1);
    }

    /**
     * @test
     */
    public function deleteFolderReturnsFalseOnEntityNotFound()
    {
        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
            ->method('find')
            ->will($this->throwException(new EntityNotFoundException()));

        $this->backend->setEntityManager($em);

        $folder = FolderItem::create(array(
            'id'        => 1,
            'parent_id' => null,
            'name'      => 'foo',
        ));

        $this->assertFalse($this->backend->deleteFolder($folder));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function updateFolderRethrowsException()
    {
        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
           ->method('getReference')
           ->will($this->throwException(new Exception()));

        $folder = FolderItem::create(array(
            'id'        => 1,
            'parent_id' => null,
            'name'      => '',
        ));

        $this->backend->setEntityManager($em);
        $this->backend->updateFolder($folder);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findSubFoldersRethrowsException()
    {
        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->throwException(new Exception()));

        $this->backend->setEntityManager($em);
        $this->backend->findSubFolders(FolderItem::create(array(
            'id'        => 1,
            'parent_id' => null,
            'name'      => 'foo',
        )));
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
     * @expectedException Xi\Filelib\FilelibException
     */
    public function deleteFileReThrowsException()
    {
        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
           ->method('remove')
           ->will($this->throwException(new Exception()));

        $this->backend->setEntityManager($em);
        $this->backend->deleteFile(FileItem::create(array('id' => 1)));
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
        return $this->getMockAndDisableOriginalConstructor(
            'Doctrine\ORM\EntityManager'
        );
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function createQueryBuilderMock()
    {
        return $this->getMockAndDisableOriginalConstructor(
            'Doctrine\ORM\QueryBuilder'
        );
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function createQueryMock()
    {
        // Mocking with PHPUnit is so easy.
        return $this->getMockForAbstractClass(
            'Doctrine\ORM\AbstractQuery',
            array(),
            '',
            false,
            true,
            true,
            array('getSingleResult')
        );
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockAndDisableOriginalConstructor($className)
    {
        return $this->getMockBuilder($className)
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
