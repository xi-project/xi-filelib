<?php

namespace Xi\Tests\Filelib\Backend;

use PHPUnit_Framework_TestCase;
use DateTime;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;

/**
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 *
 * @group backend
 */
 abstract class AbstractBackendTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Backend
     */
    protected $backend;

    /**
     * @return Backend
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

    /**
     * @dataProvider identifierValidityProvider
     * @test
     */
    public function isValidIdentifierShouldReturnCorrectResult($expected, $identifier)
    {
        $this->setUpEmptyDataSet();
        $this->assertEquals($expected, $this->backend->isValidIdentifier($identifier));
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
     * @dataProvider rootFolderIdProvider
     * @param mixed $rootFolderId
     */
    public function findRootFolderShouldReturnRootFolder($rootFolderId)
    {
        $this->setUpSimpleDataSet();

        $rootFolder = $this->backend->findFolder($rootFolderId);

        // Check that root folder exists already.
        $this->assertNull($rootFolder['parent_id']);

        $folder = $this->backend->findRootFolder();

        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('parent_id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('url', $folder);
        $this->assertArrayHasKey('uuid', $folder);
        $this->assertNull($folder['parent_id']);
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
            'hash'         => 'hashendaal',
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
     * @dataProvider findResourceProvider
     * @param integer $resourceId
     * @param array   $data
     */
    public function findResourceShouldReturnCorrectResource($resourceId, array $data)
    {
        $this->setUpSimpleDataSet();

        $resource = $this->backend->findResource($resourceId);

        $this->assertInstanceOf('Xi\Filelib\File\Resource', $resource);

        $this->assertEquals($resourceId, $resource->getId());
        $this->assertEquals($data['hash'], $resource->getHash());
        $this->assertEquals($data['versions'], $resource->getVersions());
        $this->assertNotNull($resource->getMimetype());
        $this->assertNotNull($resource->getDateCreated());
        $this->assertNotNull($resource->getSize());
        $this->assertNotNull($resource->isExclusive());
    }


    /**
     * @test
     * @dataProvider nonExistingResourceIdProvider
     * @param mixed $resourceId
     */
    public function findResourceShouldReturnFalseWhenTryingToFindNonExistingResource($resourceId)
    {
        $this->setUpEmptyDataSet();
        $this->assertFalse($this->backend->findResource($resourceId));
    }


    /**
     * @test
     * @dataProvider resourceHashProvider
     * @param mixed   $hash
     * @param integer $expectedCount
     */
    public function findResourcesByHashShouldReturnArrayOfResources($hash, $expectedCount)
    {
        $this->setUpSimpleDataSet();

        $ret = $this->backend->findResourcesByHash($hash);

        $this->assertInternalType('array', $ret);
        $this->assertCount($expectedCount, $ret);
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
            'id'        => $resourceId,
        );

        $resource = Resource::create($data);

        $this->assertInstanceOf('Xi\Filelib\File\Resource', $this->backend->findResource($resourceId));

        $this->assertTrue($this->backend->deleteResource($resource));

        $this->assertFalse($this->backend->findResource($resourceId));
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
            'id'        => $resourceId,
        );

        $resource = Resource::create($data);

        $this->assertFalse($this->backend->deleteResource($resource));

    }


    /**
     * @test
     * @dataProvider resourceIdWithReferencesProvider
     * @param mixed $resourceId
     *
     */
    public function deleteResourceThrowsExceptionWhenDeletingResourceWithReferences($resourceId)
    {
        $this->setUpSimpleDataSet();

        $resource = Resource::create(array(
            'id'        => $resourceId,
        ));

        $this->assertInstanceOf('Xi\Filelib\File\Resource', $this->backend->findResource($resourceId));

        $this->setExpectedException('Xi\Filelib\Exception\ResourceReferencedException');

        $this->backend->deleteResource($resource);
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

        $resource = $this->backend->findResource($resourceId);

        $this->assertEquals($resourceId, $resource->getId());
        $this->assertNotEquals($versions, $resource->getVersions());
        $this->assertTrue($resource->isExclusive());

        $resource->setVersions($versions);
        $resource->setExclusive(false);
        $this->assertTrue($this->backend->updateResource($resource));

        $resource2 = $this->backend->findResource($resourceId);
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
     */
    public function findRootFolderCreatesRootFolderIfItDoesNotExist()
    {
        $this->setUpEmptyDataSet();

        $folder = $this->backend->findRootFolder();

        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('parent_id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('url', $folder);
        $this->assertArrayHasKey('uuid', $folder);

        $this->assertNull($folder['parent_id']);
    }

    /**
     * @test
     * @dataProvider findFolderProvider
     * @param integer $folderId
     * @param array   $data
     */
    public function findFolderShouldReturnCorrectFolder($folderId, array $data)
    {
        $this->setUpSimpleDataSet();

        $folder = $this->backend->findFolder($folderId);

        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('parent_id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('url', $folder);
        $this->assertArrayHasKey('uuid', $folder);

        $this->assertEquals($folderId, $folder['id']);
        $this->assertEquals($data['name'], $folder['name']);
    }

    /**
     * @test
     * @dataProvider nonExistingFolderIdProvider
     * @param mixed $folderId
     */
    public function findFolderShouldReturnFalseWhenTryingToFindNonExistingFolder(
        $folderId
    ) {
        $this->setUpEmptyDataSet();

        $this->assertFalse($this->backend->findFolder($folderId));
    }

    /**
     * @test
     * @dataProvider invalidFolderIdProvider
     * @param mixed  $folderId
     * @param string $validType
     */
    public function findFolderThrowsExceptionWhenTryingToFindFolderWithInvalidIdentifier(
        $folderId, $validType
    ) {
        $this->setUpEmptyDataSet();

        $this->expectInvalidArgumentExceptionForInvalidFolderId($folderId, $validType);

        $this->backend->findFolder($folderId);
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
            'name'      => 'lusander',
            'url'       => 'lussuttaja/tussin/lusander',
            'uuid'      => 'uuid-f-566',
        );

        $folder = Folder::create($data);

        $this->assertNull($folder->getId());

        $this->assertNotNull($this->backend->createFolder($folder)->getId());
    }

    /**
     * @test
     * @dataProvider notFoundFolderIdProvider
     * @param mixed $folderId
     */
    public function createFolderThrowsExceptionWhenGivenParentFolderIdIsNotFound(
        $folderId
    ) {
        $this->setUpEmptyDataSet();

        $data = array(
            'parent_id' => $folderId,
            'name'      => 'lusander',
            'url'       => 'lussuttaja/tussin/lusander',
            'uuid'      => 'sika-f-uuid'
        );

        $this->setExpectedException(
            'Xi\Filelib\Exception\FolderNotFoundException',
            sprintf('Parent folder was not found with id "%s"', $folderId)
        );

        $this->backend->createFolder(Folder::create($data));
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
            'id'        => $folderId,
            'parent_id' => null,
            'name'      => 'klus',
        );

        $folder = Folder::create($data);

        $this->assertInternalType('array', $this->backend->findFolder($folderId));

        $this->assertTrue($this->backend->deleteFolder($folder));

        $this->assertFalse($this->backend->findFolder($folderId));
    }

    /**
     * @test
     * @dataProvider folderIdWithFilesProvider
     * @param mixed $folderId
     *
     * TODO: Is this actually what we want? How should one actually delete a
     *       folder with all files?
     */
    public function deleteFolderThrowsExceptionWhenDeletingFolderWithFiles(
        $folderId
    ) {
        $this->setUpSimpleDataSet();

        $folder = Folder::create(array(
            'id'        => $folderId,
            'parent_id' => null,
            'name'      => 'klus',
        ));

        $this->assertInternalType('array', $this->backend->findFolder($folderId));

        $this->setExpectedException(
            'Xi\Filelib\Exception\FolderNotEmptyException',
            'Can not delete folder with files'
        );

        $this->backend->deleteFolder($folder);
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
            'id'        => $folderId,
            'parent_id' => null,
            'name'      => 'klus',
        ));

        $this->assertFalse($this->backend->deleteFolder($folder));
    }

    /**
     * @test
     * @dataProvider updateFolderProvider
     * @param mixed $folderId
     * @param mixed $parentFolderId
     * @param mixed $updatedParentFolderId
     */
    public function updateFolderShouldUpdateFolder($folderId, $parentFolderId,
        $updatedParentFolderId
    ) {
        $this->setUpSimpleDataSet();

        $data = array(
            'id'        => $folderId,
            'parent_id' => $parentFolderId,
            'url'       => 'lussuttaja/tussin',
            'name'      => 'tussin',
            'uuid'      => 'uuid-f-' . $folderId,
        );

        $this->assertEquals($data, $this->backend->findFolder($folderId));

        $updateData = array(
            'id'        => $folderId,
            'parent_id' => $updatedParentFolderId,
            'url'       => 'lussuttaja/lussander',
            'name'      => 'lussander',
            'uuid'      => 'sika-uuid',
        );

        $folder = Folder::create($updateData);

        $this->assertTrue($this->backend->updateFolder($folder));
        $this->assertEquals($updateData, $this->backend->findFolder($folderId));
    }

    /**
     * @test
     * @dataProvider rootFolderIdProvider
     * @param mixed $folderId
     */
    public function updatesRootFolder($folderId)
    {
        $this->setUpSimpleDataSet();

        $data = array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => 'foo/bar',
            'name'      => 'xoo',
            'uuid'      => 'tussi-uuid',
        );

        $folder = Folder::create($data);

        $this->assertTrue($this->backend->updateFolder($folder));
        $this->assertEquals($data, $this->backend->findFolder($folderId));
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
     * @test
     * @dataProvider invalidFolderIdProvider
     * @param mixed  $folderId
     * @param string $validType
     */
    public function updateFolderThrowsExceptionWhenUpdatingFolderWithInvalidIdentifier(
        $folderId, $validType
    ) {
        $this->setUpEmptyDataSet();

        $folder = Folder::create(array(
            'id'        => $folderId,
            'parent_id' => 'xoo',
            'url'       => '',
            'name'      => '',
            'uuid'      => 'sika-uuid',
        ));

        $this->expectInvalidArgumentExceptionForInvalidFolderId($folderId, $validType);

        $this->backend->updateFolder($folder);
    }

    /**
     * @test
     * @dataProvider subFolderProvider
     * @param mixed   $folderId
     * @param integer $subFoldersCount
     */
    public function findSubFoldersShouldReturnArrayOfSubFolders($folderId,
        $subFoldersCount
    ) {
        $this->setUpSimpleDataSet();

        $folder = Folder::create(array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findSubFolders($folder);

        $this->assertInternalType('array', $ret);
        $this->assertCount($subFoldersCount, $ret);
    }

    /**
     * @test
     * @dataProvider invalidFolderIdProvider
     * @param mixed  $folderId
     * @param string $validType
     */
    public function findSubFoldersThrowsExceptionForFolderWithInvalidIdentifier(
        $folderId, $validType
    ) {
        $this->setUpEmptyDataSet();

        $folder = Folder::create(array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $this->expectInvalidArgumentExceptionForInvalidFolderId($folderId, $validType);

        $this->backend->findSubFolders($folder);
    }

    /**
     * @test
     * @dataProvider folderByUrlProvider
     * @param string $folderUrl
     * @param mixed  $folderId
     */
    public function findFolderByUrlShouldReturnFolder($folderUrl, $folderId)
    {
        $this->setUpSimpleDataSet();

        $ret = $this->backend->findFolderByUrl($folderUrl);

        $this->assertInternalType('array', $ret);
        $this->assertEquals($folderId, $ret['id']);
    }

    /**
     * @test
     */
    public function findFolderByUrlShouldNotReturnNonExistingFolder()
    {
        $this->setUpEmptyDataSet();

        $this->assertFalse(
            $this->backend->findFolderByUrl('lussuttaja/tussinnnnn')
        );
    }

    /**
     * @test
     * @dataProvider invalidFolderUrlProvider
     * @param mixed $url
     */
    public function findFolderByUrlShouldThrowExceptionIfUrlIsNotAString($url)
    {
        $this->setUpEmptyDataSet();

        $this->setExpectedException(
            'Xi\Filelib\Exception\InvalidArgumentException',
            sprintf('Folder URL must be a string, %s given', gettype($url))
        );

        $this->backend->findFolderByUrl($url);
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
     * @dataProvider findFilesInProvider
     * @param mixed   $folderId
     * @param integer $filesInFolder
     */
    public function findFilesInShouldReturnArrayOfFiles($folderId,
        $filesInFolder
    ) {
        $this->setUpSimpleDataSet();

        $folder = Folder::create(array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findFilesIn($folder);

        $this->assertInternalType('array', $ret);
        $this->assertCount($filesInFolder, $ret);
    }

    /**
     * @test
     * @dataProvider invalidFolderIdProvider
     * @param mixed  $folderId
     * @param string $validType
     */
    public function findFilesInThrowsExceptionWithInvalidFolderIdentifier(
        $folderId, $validType
    ) {
        $this->setUpEmptyDataSet();

        $folder = Folder::create(array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $this->expectInvalidArgumentExceptionForInvalidFolderId($folderId, $validType);

        $this->backend->findFilesIn($folder);
    }

    /**
     * @test
     * @dataProvider findFileProvider
     * @param mixed $fileId
     */
    public function findFileShouldReturnFile($fileId)
    {
        $this->setUpSimpleDataSet();

        $ret = $this->backend->findFile($fileId);

        $this->assertInternalType('array', $ret);

        $this->assertArrayHasKey('id', $ret);
        $this->assertArrayHasKey('folder_id', $ret);
        $this->assertArrayHasKey('profile', $ret);
        $this->assertArrayHasKey('name', $ret);
        $this->assertArrayHasKey('link', $ret);
        $this->assertArrayHasKey('date_created', $ret);
        $this->assertArrayHasKey('status', $ret);
        $this->assertArrayHasKey('resource', $ret);
        $this->assertArrayHasKey('uuid', $ret);
        $this->assertArrayHasKey('versions', $ret);

        $this->assertInternalType('array', $ret['versions']);
        $this->assertInstanceOf('Xi\Filelib\File\Resource', $ret['resource']);
        $this->assertInstanceOf('DateTime', $ret['date_created']);
    }

    /**
     * @test
     * @dataProvider findFileProvider
     * @param mixed $fileId
     */
    public function findFileReturnsFalseIfFileIsNotFound($fileId)
    {
        $this->setUpEmptyDataSet();

        $this->assertFalse($this->backend->findFile($fileId));
    }

    /**
     * @test
     * @dataProvider invalidFileIdProvider
     * @param mixed  $fileId
     * @param string $validType
     */
    public function findFileThrowsExceptionWithInvalidIdentifier($fileId,
        $validType
    ) {
        $this->setUpEmptyDataSet();

        $this->expectInvalidArgumentExceptionForInvalidFileId($fileId, $validType);

        $this->backend->findFile($fileId);
    }

    /**
     * @test
     */
    public function findAllFilesShouldReturnAllFiles()
    {
        $this->setUpSimpleDataSet();

        $rets = $this->backend->findAllFiles();

        $this->assertInternalType('array', $rets);
        $this->assertCount(5, $rets);

        foreach ($rets as $ret) {
            $this->assertInternalType('array', $ret);

            $this->assertArrayHasKey('id', $ret);
            $this->assertArrayHasKey('folder_id', $ret);
            $this->assertArrayHasKey('profile', $ret);
            $this->assertArrayHasKey('name', $ret);
            $this->assertArrayHasKey('link', $ret);
            $this->assertArrayHasKey('date_created', $ret);
            $this->assertArrayHasKey('status', $ret);
            $this->assertArrayHasKey('resource', $ret);
            $this->assertArrayHasKey('uuid', $ret);
            $this->assertArrayHasKey('versions', $ret);

            $this->assertInternalType('array', $ret['versions']);
            $this->assertInstanceOf('Xi\Filelib\File\Resource', $ret['resource']);
            $this->assertInstanceOf('DateTime', $ret['date_created']);
        }
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
            'id'            => $fileId,
            'folder_id'     => $folderId,
            'profile'       => 'lussed',
            'name'          => 'tohtori-sykero.png',
            'link'          => 'tohtori-sykero.png',
            'date_created' => new DateTime('2011-01-02 16:16:16'),
            'status'        => 666,
            'uuid'          => 'uuid-535',
            'resource'      => $this->backend->findResource($resourceId),
            'versions'      => array('lussi', 'watussi', 'klussi'),
        );

        $file = File::create($data);

        $this->assertTrue($this->backend->updateFile($file));

        $updated = $this->backend->findFile($fileId);

        $this->assertEquals($data['resource']->getId(), $updated['resource']->getId());

        $fields = array('id', 'folder_id', 'profile', 'name', 'link', 'date_created', 'status', 'uuid', 'versions');
        foreach ($fields as $field) {
            $this->assertEquals($data[$field], $updated[$field]);
        }
    }

    /**
     * @test
     * @dataProvider notFoundFolderIdProvider
     * @param mixed $folderId
     */
    public function updateFileThrowsExceptionWithNotFoundFolder($folderId)
    {
        $this->setUpSimpleDataSet();

        $updated = array(
            'id'            => 1,
            'folder_id'     => $folderId,
            'profile'       => 'lussed',
            'name'          => 'tohtori-sykero.png',
            'link'          => 'tohtori-sykero.png',
            'date_created' => new DateTime('2011-01-02 16:16:16'),
            'status'        => 4,
            'uuid'          => 'uuid-1',
            'resource'      => Resource::create(array('id' => 1, 'hash' => 'hash-1', 'date_created' => new DateTime('1978-03-21 06:06:06'))),
            'versions'      => array(),
        );

        $file = File::create($updated);

        $this->setExpectedException(
            'Xi\Filelib\Exception\FolderNotFoundException',
            sprintf('Folder was not found with id "%s"', $folderId)
        );

        $this->backend->updateFile($file);
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
        $this->assertFalse($this->backend->findFile($fileId));
    }

    /**
     * @test
     * @dataProvider invalidFileIdProvider
     * @param mixed  $fileId
     * @param string $validType
     */
    public function deleteFileThrowsExceptionWithInvalidIdentifier($fileId,
        $validType
    ) {
        $this->setUpEmptyDataSet();

        $file = File::create(array('id' => $fileId));

        $this->expectInvalidArgumentExceptionForInvalidFileId($fileId, $validType);

        $this->backend->deleteFile($file);
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
    public function fileUploadShouldUploadFile($folderId)
    {
        $this->setUpSimpleDataSet();

        $fidata = array(
            'profile'       => 'versioned',
            'name'          => 'tohtori-tussi.png',
            'link'          => 'tohtori-tussi.png',
            'date_created'  => new DateTime('2011-01-01 16:16:16'),
            'status'        => 5,
            'uuid'          => 'uuid-lussid',
            'resource'      => Resource::create(array('id' => 1)),
            'versions'      => array(),
        );

        $fodata = array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        );

        $file = File::create($fidata);
        $folder = Folder::create($fodata);

        $file = $this->backend->upload($file, $folder);

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
     * @dataProvider notFoundFolderIdProvider
     * @param mixed $folderId
     */
    public function fileUploadThrowsExceptionWithNotFoundFolder($folderId)
    {
        $this->setUpEmptyDataSet();

        $file = File::create(array(
            'profile'       => 'versioned',
            'name'          => 'tohtori-tussi.png',
            'link'          => 'tohtori-tussi.png',
            'date_created'  => new DateTime('2011-01-01 16:16:16'),
            'status'        => 3,
            'uuid'          => 'uuid-lussid',
            'resource'      => Resource::create(array('id' => 1)),
            'versions'      => array(),
        ));

        $folder = Folder::create(array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $this->setExpectedException(
            'Xi\Filelib\Exception\FolderNotFoundException',
            sprintf('Folder was not found with id "%s"', $folderId)
        );

        $this->backend->upload($file, $folder);
    }

    /**
     * @test
     * @dataProvider folderIdProvider
     * @param mixed $folderId
     */
    public function fileUploadShouldThrowExceptionWithAlreadyExistingFile(
        $folderId
    ) {
        $this->setUpSimpleDataSet();

        $file = File::create(array(
            'profile'       => 'versioned',
            'name'          => 'tohtori-vesala.png',
            'link'          => 'tohtori-vesala.png',
            'date_created'  => new DateTime('2011-01-01 16:16:16'),
            'status'        => 4,
            'uuid'          => 'uuid-lussid',
            'resource'      => Resource::create(array('id' => 1)),
            'versions'      => array('na-na-naa-naa'),
        ));

        $folder = Folder::create(array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => 'root',
        ));

        $this->setExpectedException(
            'Xi\Filelib\Exception\NonUniqueFileException',
            sprintf(
                'A file with the name "%s" already exists in folder "%s"',
                'tohtori-vesala.png',
                'root'
            )
        );

        $this->backend->upload($file, $folder);
    }

    /**
     * @test
     * @dataProvider findFileByFilenameProvider
     * @param mixed $fileId
     * @param mixed $folderId
     */
    public function findFileByFilenameShouldReturnCorrectFile($fileId, $folderId, $resourceId)
    {
        $this->setUpSimpleDataSet();

        $fidata = array(
            'profile'       => 'versioned',
            'name'          => 'tohtori-vesala.png',
            'link'          => 'tohtori-vesala.png',
            'date_created' => new DateTime('2011-01-01 16:16:16'),
            'id'            => $fileId,
            'folder_id'     => $folderId,
            'status'        => 1,
            'uuid'          => 'uuid-1',
            'resource'   => Resource::create(array('id' => $resourceId)),
       );

        $fodata = array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        );

        $folder = Folder::create($fodata);

        $file = $this->backend->findFileByFileName($folder, 'tohtori-vesala.png');

        $this->assertInternalType('array', $file);

        $this->assertEquals($folder->getId(), $file['folder_id']);
        $this->assertEquals($fidata['name'], $file['name']);

    }

    /**
     * @test
     * @dataProvider folderIdProvider
     * @param mixed $folderId
     */
    public function findFileByFilenameShouldNotFindNonExistingFile($folderId)
    {
        $this->setUpSimpleDataSet();

        $folder = Folder::create(array(
            'id'        => $folderId,
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
     * @dataProvider invalidFolderIdProvider
     * @param mixed  $folderId
     * @param string $validType
     */
    public function findFileByFileNameThrowsExceptionWithInvalidFolderIdentifier(
        $folderId, $validType
    ) {
        $this->setUpEmptyDataSet();

        $folder = Folder::create(array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $this->expectInvalidArgumentExceptionForInvalidFolderId($folderId, $validType);

        $this->backend->findFileByFileName($folder, 'tohtori-tussi.png');
    }

    /**
     * @param mixed  $fileId
     * @param string $validType
     */
    private function expectInvalidArgumentExceptionForInvalidFileId($fileId,
        $validType
    ) {
        $this->setExpectedException(
            'Xi\Filelib\Exception\InvalidArgumentException'
        );
    }

    /**
     * @param mixed  $folderId
     * @param string $validType
     */
    private function expectInvalidArgumentExceptionForInvalidFolderId($folderId,
        $validType
    ) {
        $this->setExpectedException(
            'Xi\Filelib\Exception\InvalidArgumentException'
        );
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


}
