<?php

namespace Xi\Filelib\Tests\Backend\Platform;

use Xi\Filelib\Backend\Platform\DoctrineOrmPlatform;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Resource;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @group backend
 * @group doctrine
 */
class DoctrineOrmPlatformTest extends RelationalDbTestCase
{

    /**
     * @return DoctrineOrmPlatform
     */
    protected function setUpBackend()
    {
        $cache = new ArrayCache();

        AnnotationRegistry::registerFile(
            ROOT_TESTS . '/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
        );

        $driver = new AnnotationDriver(
            new CachedReader(new AnnotationReader(), $cache),
            array(
                ROOT_TESTS . '/../library/Xi/Filelib/Backend/DoctrineOrm/Entity',
            )
        );

        $config = new Configuration();
        $config->setMetadataCacheImpl($cache);
        $config->setMetadataDriverImpl($driver);
        $config->setQueryCacheImpl($cache);
        $config->setProxyDir(ROOT_TESTS . '/data/temp');
        $config->setProxyNamespace('FilelibTest\Proxies');
        $config->setAutoGenerateProxyClasses(true);

        $connectionOptions = PDO_DRIVER === 'sqlite'
            ? array(
                'driver' => 'pdo_' . PDO_DRIVER,
                'path' => PDO_DBNAME,
            )
            : array(
                'driver' => 'pdo_' . PDO_DRIVER,
                'dbname' => PDO_DBNAME,
                'user' => PDO_USERNAME,
                'password' => PDO_PASSWORD,
                'host' => PDO_HOST,
            );

        $em = EntityManager::create($connectionOptions, $config);

        return new DoctrineOrmPlatform($em);
    }

    /**
     * @test
     */
    public function entityClassGettersShouldReturnCorrectClassNames()
    {
        $this->setUpEmptyDataSet();

        $this->assertEquals(
            'Xi\Filelib\Backend\Platform\DoctrineOrm\Entity\File',
            $this->backend->getFileEntityName()
        );

        $this->assertEquals(
            'Xi\Filelib\Backend\Platform\DoctrineOrm\Entity\Folder',
            $this->backend->getFolderEntityName()
        );

        $this->assertEquals(
            'Xi\Filelib\Backend\Platform\DoctrineOrm\Entity\Resource',
            $this->backend->getResourceEntityName()
        );
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

        $backend = new DoctrineOrmPlatform($em);

        $resource = Folder::create(
            array(
                'id' => 666,
                'parent_id' => null,
                'name' => 'foo',
            )
        );

        $this->assertFalse($backend->deleteFolder($resource));
    }

    /**
     * @test
     */
    public function deleteResourceReturnsFalseOnEntityNotFound()
    {
        $this->setUpEmptyDataSet();

        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
            ->method('find')
            ->will($this->throwException(new EntityNotFoundException()));

        $backend = new DoctrineOrmPlatform($em);

        $resource = Resource::create(array('id' => 1));

        $this->assertFalse($backend->deleteResource($resource));
    }

    /**
     * @param EntityManager $em
     */
    private function returnEmptyArrayForFindFilesInFolder(EntityManager $em)
    {
        $repository = $this->getMockAndDisableOriginalConstructor(
            'Doctrine\ORM\EntityRepository'
        );
        $repository
            ->expects($this->once())
            ->method('findBy')
            ->will($this->returnValue(array()));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));
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
     * @test
     */
    public function entityManagerGetterShouldWork()
    {
        $em = $this->createEntityManagerMock();
        $platform = new DoctrineOrmPlatform($em);

        $this->assertSame($em, $platform->getEntityManager());
    }
}
