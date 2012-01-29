<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Backend\Doctrine2Backend,
    Xi\Filelib\Folder\FolderItem,
    Xi\Filelib\File\FileItem,
    Exception,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration,
    Doctrine\ORM\EntityNotFoundException,
    Doctrine\Common\Cache\ArrayCache,
    PHPUnit_Framework_MockObject_MockObject;

/**
 * @group doctrine
 */
class Doctrine2BackendTest extends RelationalDbTestCase
{
    /**
     * @return Doctrine2Backend
     */
    protected function setUpBackend()
    {
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

        $backend = new Doctrine2Backend();
        $backend->setEntityManager($em);

        return $backend;
    }

    /**
     * @test
     */
    public function entityClassGettersShouldReturnCorrectClassNames()
    {
        $this->setUpEmptyDataSet();

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
        $this->setUpEmptyDataSet();

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
        $this->setUpEmptyDataSet();

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
        $this->setUpEmptyDataSet();

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
        $this->setUpEmptyDataSet();

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
        $this->setUpEmptyDataSet();

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
        $this->setUpEmptyDataSet();

        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
           ->method('find')
           ->will($this->returnValue(null));

        $this->backend->setEntityManager($em);

        $this->assertFalse($this->backend->findFile(1));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function deleteFileReThrowsException()
    {
        $this->setUpEmptyDataSet();

        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
           ->method('remove')
           ->will($this->throwException(new Exception()));

        $this->backend->setEntityManager($em);
        $this->backend->deleteFile(FileItem::create(array('id' => 1)));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findFileByFileNameRethrowsException()
    {
        $this->setUpEmptyDataSet();

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
        $this->setUpEmptyDataSet();

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
        $this->setUpEmptyDataSet();

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
        $this->setUpEmptyDataSet();

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
        $this->setUpEmptyDataSet();

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
    private function getMockAndDisableOriginalConstructor($className)
    {
        return $this->getMockBuilder($className)
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
