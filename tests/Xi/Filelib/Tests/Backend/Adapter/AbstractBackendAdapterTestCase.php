<?php

namespace Xi\Filelib\Tests\Backend\Adapter;

use PHPUnit_Framework_TestCase;
use DateTime;
use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\Backend\Adapter\BackendAdapter;
use Xi\Filelib\File\File;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Backend\Finder\Finder;

/**
 * @group backend
 */
abstract class AbstractBackendAdapterTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var BackendAdapter
     */
    protected $backend;

    /**
     * @return BackendAdapter
     */
    abstract protected function setUpBackend();

    /**
     * Set up a test using an empty data set.
     */
    abstract protected function setUpEmptyDataSet();

    /**
     * Set up a test using a simple data set.
     */
    abstract protected function setUpSimpleDataSet();

    abstract protected function assertValidCreatedIdentifier($identifier);

    protected function setUp()
    {
        $this->backend = $this->setUpBackend();
    }

    protected function tearDown()
    {
        // Unset to keep database connections from piling up.
        $this->backend = null;

        // Collect garbages manually to free up connections.
        gc_collect_cycles();
    }

    /**
     * @test
     * @dataProvider referenceCountProvider
     * @param integer $numberOfReferences
     */
    public function getNumberOfReferencesShouldReturnCorrectCount($numberOfReferences, $resourceId)
    {
        $this->setUpSimpleDataSet();
        $resource = ConcreteResource::create(array('id' => $resourceId));
        $this->assertEquals($numberOfReferences, $this->backend->getNumberOfReferences($resource));
    }

    /**
     * @test
     */
    public function createResourceShouldCreateResource()
    {
        $this->setUpSimpleDataSet();

        $data = array(
            'hash' => 'hashendaal',
            'uuid' => 'uuid-xooxers',
            'date_created' => new DateTime('2010-10-10 10:10:10'),
            'size' => 6000,
            'mimetype' => 'lussuta/tussia',
            'exclusive' => true,
            'data' => array(
                'grande' => 'lusso',
                'versions' => array('loso', 'puuppa'),
            )
        );

        $resource = ConcreteResource::create($data);
        $this->assertNull($resource->getId());
        $this->backend->createResource($resource);

        $this->assertValidCreatedIdentifier($resource->getId());
    }

    /**
     * @test
     * @dataProvider orphanResourceIdProvider
     * @param mixed $resourceId
     */
    public function deleteResourceShouldDeleteResource($resourceId)
    {
        $this->setUpSimpleDataSet();

        $data = array(
            'id' => $resourceId,
        );

        $resource = ConcreteResource::create($data);

        $this->assertInstanceOf('Xi\Filelib\Resource\ConcreteResource', $this->findResource($resourceId));

        $this->assertTrue($this->backend->deleteResource($resource));
        $this->assertFalse($this->findResource($resourceId));
    }

    /**
     * @test
     * @dataProvider nonexistingResourceIdProvider
     * @param mixed $resourceId
     */
    public function deleteResourceShouldNotDeleteNonexistingResource($resourceId)
    {
        $this->setUpSimpleDataSet();

        $data = array(
            'id' => $resourceId,
        );

        $resource = ConcreteResource::create($data);

        $this->assertFalse($this->backend->deleteResource($resource));
    }

    /**
     * @test
     * @dataProvider updateResourceProvider
     * @param mixed $resourceId
     * @param mixed $versions
     */
    public function updateResourceShouldUpdateResource($resourceId, $versions)
    {
        $this->setUpSimpleDataSet();

        $resource = $this->findResource($resourceId);

        $this->assertInstanceOf('Xi\Filelib\Resource\ConcreteResource', $resource);

        $this->assertNotNull($resource->getUuid());
        $this->assertEquals($resourceId, $resource->getId());
        $this->assertNotEquals($versions, $resource->getVersions());
        $this->assertTrue($resource->isExclusive());

        $expectedVersions = array_merge($resource->getVersions(), $versions);
        foreach ($versions as $version) {
            $resource->addVersion(Version::get($version), ConcreteResource::create());
        }
        $resource->setExclusive(false);
        $this->assertTrue($this->backend->updateResource($resource));

        $resource2 = $this->findResource($resourceId);
        $this->assertEquals($expectedVersions, $resource2->getVersions());
        $this->assertFalse($resource2->isExclusive());
    }

    /**
     * @test
     * @dataProvider nonExistingResourceIdProvider
     * @param mixed $resourceId
     */
    public function updateResourceShouldNotUpdateNonExistingResource($resourceId)
    {
        $this->setUpEmptyDataSet();

        $resource = ConcreteResource::create(
            array(
                'id' => $resourceId,
            )
        );
        $this->assertFalse($this->backend->updateResource($resource));
    }

    /**
     * @test
     * @dataProvider parentFolderIdProvider
     * @param mixed $parentFolderId
     */
    public function createFolderShouldCreateFolder($parentFolderId)
    {
        $this->setUpSimpleDataSet();

        $data = array(
            'parent_id' => $parentFolderId,
            'name' => 'lusander',
            'url' => 'lussuttaja/tussin/lusander',
            'uuid' => 'uuid-f-566',
        );

        $folder = Folder::create($data);

        $this->backend->createFolder($folder);
        $this->assertValidCreatedIdentifier($folder->getId());

    }

    /**
     * @test
     * @dataProvider filelessFolderIdProvider
     * @param mixed $folderId
     */
    public function deleteFolderShouldDeleteFolder($folderId)
    {
        $this->setUpSimpleDataSet();

        $data = array(
            'id' => $folderId,
            'parent_id' => null,
            'name' => 'klus',
        );

        $folder = Folder::create($data);

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $this->findFolder($folderId));
        $this->assertTrue($this->backend->deleteFolder($folder));
        $this->assertFalse($this->findFolder($folderId));
    }

    /**
     * @test
     * @dataProvider nonExistingFolderIdProvider
     * @param mixed $folderId
     */
    public function deleteFolderShouldNotDeleteNonExistingFolder($folderId)
    {
        $this->setUpEmptyDataSet();

        $folder = Folder::create(
            array(
                'id' => $folderId,
                'parent_id' => null,
                'name' => 'klus',
            )
        );

        $this->assertFalse($this->backend->deleteFolder($folder));
    }

    /**
     * @test
     * @dataProvider updateFolderProvider
     * @param mixed $folderId
     * @param mixed $parentFolderId
     * @param mixed $updatedParentFolderId
     * @group refactor
     */
    public function updateFolderShouldUpdateFolder(
        $folderId,
        $parentFolderId,
        $updatedParentFolderId
    ) {
        $this->setUpSimpleDataSet();

        $data = Folder::create(
            array(
                'id' => $folderId,
                'parent_id' => $parentFolderId,
                'url' => 'lussuttaja/tussin',
                'name' => 'tussin',
                'uuid' => 'uuid-f-' . $folderId,
                'data' => array(
                    'lusso' => array(
                        'gran-tusso' => 'libaisu',
                    )
                )
            )
        );

        $this->assertEquals($data, $this->findFolder($folderId));


        $updateData = array(
            'id' => $folderId,
            'parent_id' => $updatedParentFolderId,
            'url' => 'lussuttaja/lussander',
            'name' => 'lussander',
            'uuid' => 'sika-uuid',
            'data' => array(
                'tenhusta' => 'mina ylistan koska han on suurin kaikista suurempi kuin kim jong un'
            )
        );
        $folder = Folder::create($updateData);

        $this->assertTrue($this->backend->updateFolder($folder));
        $this->assertEquals($folder, $this->findFolder($folderId));
    }

    /**
     * @test
     * @dataProvider rootFolderIdProvider
     * @param mixed $folderId
     * @group refactor
     */
    public function updatesRootFolder($folderId)
    {
        $this->setUpSimpleDataSet();

        $folder = Folder::create(
            array(
                'id' => $folderId,
                'parent_id' => null,
                'url' => 'foo/bar',
                'name' => 'xoo',
                'uuid' => 'tussi-uuid',
            )
        );

        $this->assertTrue($this->backend->updateFolder($folder));
        $this->assertEquals($folder, $this->findFolder($folderId));
    }

    /**
     * @test
     * @dataProvider nonExistingFolderIdProvider
     * @param mixed $folderId
     */
    public function updateFolderShouldNotUpdateNonExistingFolder($folderId)
    {
        $this->setUpEmptyDataSet();

        $folder = Folder::create(
            array(
                'id' => $folderId,
            )
        );

        $this->assertFalse($this->backend->updateFolder($folder));
    }

    /**
     * @return array
     */
    public function invalidFolderUrlProvider()
    {
        return array(
            array(array()),
            array(new \stdClass()),
        );
    }

    /**
     * @test
     * @dataProvider updateFileProvider
     * @param mixed $fileId
     * @param mixed $folderId
     */
    public function updateFileShouldUpdateFile($fileId, $folderId, $resourceId)
    {
        $this->setUpSimpleDataSet();

        $data = array(
            'id' => $fileId,
            'folder_id' => $folderId,
            'profile' => 'lussed',
            'name' => 'tohtori-sykero.png',
            'link' => 'tohtori-sykero.png',
            'date_created' => new DateTime('2011-01-02 16:16:16'),
            'status' => 666,
            'uuid' => 'uuid-535',
            'resource' => $this->findResource($resourceId),
            'data' => array('versions' => array('lussi', 'watussi', 'klussi')),
        );
        $file = File::create($data);

        $this->assertTrue($this->backend->updateFile($file));

        $updated = $this->findFile($fileId);

        $this->assertEquals($file, $updated);
    }

    /**
     * @test
     * @dataProvider deleteFileProvider
     * @param mixed $fileId
     */
    public function deleteFileShouldDeleteFile($fileId)
    {
        $this->setUpSimpleDataSet();

        $file = File::create(array('id' => $fileId));

        $this->assertTrue($this->backend->deleteFile($file));
        $this->assertFalse($this->findFile($fileId));
    }

    /**
     * @test
     * @dataProvider deleteFileProvider
     * @param mixed $fileId
     */
    public function deleteFileReturnsFalseIfFileIsNotFound($fileId)
    {
        $this->setUpEmptyDataSet();

        $file = File::create(array('id' => $fileId));

        $this->assertFalse($this->backend->deleteFile($file));
    }

    /**
     * @test
     * @dataProvider folderIdProvider
     * @param mixed $folderId
     */
    public function fileCreateShouldCreateFile($folderId)
    {
        $this->setUpSimpleDataSet();

        $fidata = array(
            'profile' => 'versioned',
            'name' => 'tohtori-tussi.png',
            'link' => 'tohtori-tussi.png',
            'date_created' => new DateTime('2011-01-01 16:16:16'),
            'status' => 5,
            'uuid' => 'uuid-lussid',
            'resource' => ConcreteResource::create(array('id' => 1)),
        );

        $fodata = array(
            'id' => $folderId,
            'parent_id' => null,
            'url' => '',
            'name' => '',
        );

        $file = File::create($fidata);
        $folder = Folder::create($fodata);


        $file = $this->backend->createFile($file, $folder);

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);
        $this->assertValidCreatedIdentifier($file->getId());

        $this->assertEquals($fodata['id'], $file->getFolderId());
        $this->assertEquals($fidata['profile'], $file->getProfile());
        $this->assertEquals($fidata['date_created'], $file->getDateCreated());
        $this->assertEquals($fidata['status'], $file->getStatus());
        $this->assertEquals($fidata['uuid'], $file->getUuid());
        $this->assertEquals($fidata['resource'], $file->getResource());
        $this->assertEquals(array(), $file->getVersions());
    }

    /**
     * @dataProvider dataPersistenceProvider
     * @test
     */
    public function dataShouldBeFetchedAndStored($id, $key, $expected)
    {
        $this->setUpSimpleDataSet();
        $file = $this->findFile($id);

        $data = $file->getData();
        $this->assertSame($expected, $data->get($key));

        $sucklingData = array('suckling' => 'on a duckling');
        $data->set('imaiseppa', $sucklingData);

        $this->backend->updateFile($file);

        $file2 = $this->findFile($id);
        $this->assertEquals($file, $file2);
    }


    /**
     * @test
     * @group finder
     * @dataProvider provideFinders
     */
    public function findingWithFinderShouldReturnExpectedAmountOfIds($expected, Finder $finder)
    {
        $this->setUpSimpleDataSet();
        $ids = $this->backend->findByFinder($finder);
        $this->assertCount($expected, $ids);

        $request = new FindByIdsRequest($ids, $finder->getResultClass());
        $objs = $this->backend->findByIds($request)->getResult();

        $this->assertCount($expected, $objs);

        foreach ($objs as $obj) {
            $this->assertInstanceOf($finder->getResultClass(), $obj);
        }
    }

    /**
     * @test
     */
    public function mustBeOriginResolver()
    {
        $this->assertTrue($this->backend->isOrigin());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockAndDisableOriginalConstructor($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $id
     * @return ConcreteResource
     */
    public function findResource($id)
    {
        $request = new FindByIdsRequest(array($id), 'Xi\Filelib\Resource\ConcreteResource');
        $ret = $this->backend->findByIds($request);
        return $ret->getResult()->first()->getOrElse(false);
    }

    /**
     * @param $id
     * @return File
     */
    public function findFile($id)
    {
        $request = new FindByIdsRequest(array($id), 'Xi\Filelib\File\File');
        $ret = $this->backend->findByIds($request);
        return $ret->getResult()->first()->getOrElse(false);
    }

    /**
     * @param $id
     * @return Folder
     */
    public function findFolder($id)
    {
        $request = new FindByIdsRequest(array($id), 'Xi\Filelib\Folder\Folder');
        $ret = $this->backend->findByIds($request);
        return $ret->getResult()->first()->getOrElse(false);
    }
}
