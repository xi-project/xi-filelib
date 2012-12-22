<?php

namespace Xi\Tests\Filelib\Backend\Platform;

use PHPUnit_Framework_TestCase;
use DateTime;
use Xi\Filelib\Backend\Platform\Platform;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Backend\Finder\Finder;

/**
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 *
 * @group backend
 */
abstract class AbstractPlatformTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var Platform
     */
    protected $backend;

    /**
     * @return Platform
     */
    protected abstract function setUpBackend();

    /**
     * Set up a test using an empty data set.
     */
    protected abstract function setUpEmptyDataSet();

    /**
     * Set up a test using a simple data set.
     */
    protected abstract function setUpSimpleDataSet();

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
     *
     * @test
     */
    public function generateUuidShouldGenerateUuid()
    {
        $this->setUpEmptyDataSet();
        $uuid = $this->backend->generateUuid();
        $this->assertRegExp("/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/", $uuid);
    }


    /**
     * @test
     * @dataProvider referenceCountProvider
     * @param integer $numberOfReferences
     */
    public function getNumberOfReferencesShouldReturnCorrectCount($numberOfReferences, $resourceId)
    {
        $this->setUpSimpleDataSet();
        $resource = Resource::create(array('id' => $resourceId));

        $this->setUpSimpleDataSet();
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
            'date_created' => new DateTime('2010-10-10 10:10:10'),
            'versions' => array('loso', 'puuppa'),
            'size' => 6000,
            'mimetype' => 'lussuta/tussia',
            'exclusive' => true,
        );

        $resource = Resource::create($data);
        $this->assertNull($resource->getId());

        $this->assertNotNull($this->backend->createResource($resource)->getId());
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

        $resource = Resource::create($data);

        $this->assertInstanceOf('Xi\Filelib\File\Resource', $this->findResource($resourceId));

        $this->assertTrue($this->backend->deleteResource($resource));

        $this->assertNull($this->findResource($resourceId));
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

        $resource = Resource::create($data);

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

        $this->assertInstanceOf('Xi\Filelib\File\Resource', $resource);

        $this->assertEquals($resourceId, $resource->getId());
        $this->assertNotEquals($versions, $resource->getVersions());
        $this->assertTrue($resource->isExclusive());

        $resource->setVersions($versions);
        $resource->setExclusive(false);
        $this->assertTrue($this->backend->updateResource($resource));

        $resource2 = $this->findResource($resourceId);
        $this->assertEquals($versions, $resource2->getVersions());
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

        $resource = Resource::create(array(
            'id' => $resourceId,
        ));
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

        $this->assertNull($folder->getId());

        $this->assertNotNull($this->backend->createFolder($folder)->getId());
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
        $this->assertNull($this->findFolder($folderId));
    }

    /**
     * @test
     * @dataProvider nonExistingFolderIdProvider
     * @param mixed $folderId
     */
    public function deleteFolderShouldNotDeleteNonExistingFolder($folderId)
    {
        $this->setUpEmptyDataSet();

        $folder = Folder::create(array(
            'id' => $folderId,
            'parent_id' => null,
            'name' => 'klus',
        ));

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
    public function updateFolderShouldUpdateFolder($folderId, $parentFolderId,
                                                   $updatedParentFolderId
    )
    {
        $this->setUpSimpleDataSet();

        $data = Folder::create(array(
            'id' => $folderId,
            'parent_id' => $parentFolderId,
            'url' => 'lussuttaja/tussin',
            'name' => 'tussin',
            'uuid' => 'uuid-f-' . $folderId,
        ));

        $this->assertEquals($data, $this->findFolder($folderId));

        $updateData = array(
            'id' => $folderId,
            'parent_id' => $updatedParentFolderId,
            'url' => 'lussuttaja/lussander',
            'name' => 'lussander',
            'uuid' => 'sika-uuid',
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

        $folder = Folder::create(array(
            'id' => $folderId,
            'parent_id' => null,
            'url' => 'foo/bar',
            'name' => 'xoo',
            'uuid' => 'tussi-uuid',
        ));

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

        $folder = Folder::create(array(
            'id' => $folderId,
        ));

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
            'versions' => array('lussi', 'watussi', 'klussi'),
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
        $this->assertNull($this->findFile($fileId));
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
            'resource' => Resource::create(array('id' => 1)),
            'versions' => array(),
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
        $this->assertNotNull($file->getId());

        $this->assertEquals($fodata['id'], $file->getFolderId());
        $this->assertEquals($fidata['profile'], $file->getProfile());
        $this->assertEquals($fidata['link'], $file->getLink());
        $this->assertEquals($fidata['date_created'], $file->getDateCreated());
        $this->assertEquals($fidata['status'], $file->getStatus());
        $this->assertEquals($fidata['uuid'], $file->getUuid());
        $this->assertEquals($fidata['resource'], $file->getResource());
        $this->assertEquals($fidata['versions'], $file->getVersions());
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

        $objs = $this->backend->findByIds($ids, $finder->getResultClass());
        $this->assertCount($expected, $objs);

        foreach ($objs as $obj) {
            $this->assertInstanceOf($finder->getResultClass(), $obj);
        }
    }


    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockAndDisableOriginalConstructor($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }


    public function findResource($id)
    {
        $ret = $this->backend->findByIds(array($id), 'Xi\Filelib\File\Resource');
        return $ret->current();
    }

    public function findFile($id)
    {
        $ret = $this->backend->findByIds(array($id), 'Xi\Filelib\File\File');
        return $ret->current();
    }

    public function findFolder($id)
    {
        $ret = $this->backend->findByIds(array($id), 'Xi\Filelib\Folder\Folder');
        return $ret->current();
    }


}
