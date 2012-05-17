<?php

namespace Xi\Tests\Filelib\Backend;

use PHPUnit_Framework_TestCase;
use DateTime;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\File\FileItem;
use Xi\Filelib\Folder\FolderItem;

/**
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
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

        $this->assertNull($folder['parent_id']);
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

        $this->assertEquals($folderId, $folder['id']);
        $this->assertEquals($data['name'], $folder['name']);
    }

    /**
     * @test
     * @dataProvider nonExistingFolderIdProvider
     * @param mixed $folderId
     */
    public function findFolderShouldReturnNullWhenTryingToFindNonExistingFolder(
        $folderId
    ) {
        $this->setUpEmptyDataSet();

        $this->assertFalse($this->backend->findFolder($folderId));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     * @dataProvider invalidFolderIdProvider
     * @param mixed $folderId
     */
    public function findFolderThrowsExceptionWhenTryingToFindFolderWithInvalidIdentifier(
        $folderId
    ) {
        $this->setUpEmptyDataSet();

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
        );

        $folder = FolderItem::create($data);

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
        );

        $this->setExpectedException(
            'Xi\Filelib\FilelibException',
            sprintf('Parent folder was not found with id "%s"', $folderId)
        );

        $this->backend->createFolder(FolderItem::create($data));
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

        $folder = FolderItem::create($data);

        $this->assertInternalType('array', $this->backend->findFolder($folderId));

        $this->assertTrue($this->backend->deleteFolder($folder));

        $this->assertFalse($this->backend->findFolder($folderId));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function deleteFolderShouldThrowExceptionWhenDeletingFolderWithFiles()
    {
        $this->setUpSimpleDataSet();

        $data = array(
            'id'        => 4,
            'parent_id' => null,
            'name'      => 'klus',
        );

        $folder = FolderItem::create($data);

        $this->assertInternalType('array', $this->backend->findFolder(5));

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

        $folder = FolderItem::create(array(
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
        );

        $this->assertEquals($data, $this->backend->findFolder($folderId));

        $updateData = array(
            'id'        => $folderId,
            'parent_id' => $updatedParentFolderId,
            'url'       => 'lussuttaja/lussander',
            'name'      => 'lussander',
        );

        $folder = FolderItem::create($updateData);

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
        );

        $folder = FolderItem::create($data);

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

        $folder = FolderItem::create(array(
            'id' => $folderId,
        ));

        $this->assertFalse($this->backend->updateFolder($folder));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     * @dataProvider invalidFolderIdProvider
     * @param mixed $folderId
     */
    public function updateFolderThrowsExceptionWhenUpdatingFolderWithInvalidIdentifier(
        $folderId
    ) {
        $this->setUpEmptyDataSet();

        $folder = FolderItem::create(array(
            'id'        => $folderId,
            'parent_id' => 'xoo',
            'url'       => '',
            'name'      => '',
        ));

        $this->assertFalse($this->backend->updateFolder($folder));
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

        $folder = FolderItem::create(array(
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
     * @expectedException Xi\Filelib\FilelibException
     * @dataProvider invalidFolderIdProvider
     * @param mixed $folderId
     */
    public function findSubFoldersThrowsExceptionForFolderWithInvalidIdentifier(
        $folderId
    ) {
        $this->setUpEmptyDataSet();

        $folder = FolderItem::create(array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

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
     * @expectedException Xi\Filelib\FilelibException
     * @dataProvider invalidFolderUrlProvider
     * @param mixed $url
     */
    public function findFolderByUrlShouldThrowExceptionIfUrlIsNotAString($url)
    {
        $this->setUpEmptyDataSet();

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

        $folder = FolderItem::create(array(
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
     * @expectedException Xi\Filelib\FilelibException
     * @dataProvider invalidFolderIdProvider
     * @param mixed $folderId
     */
    public function findFilesInThrowsExceptionWithInvalidFolderIdentifier(
        $folderId
    ) {
        $this->setUpEmptyDataSet();

        $folder = FolderItem::create(array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

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
        $this->assertArrayHasKey('mimetype', $ret);
        $this->assertArrayHasKey('profile', $ret);
        $this->assertArrayHasKey('size', $ret);
        $this->assertArrayHasKey('name', $ret);
        $this->assertArrayHasKey('link', $ret);
        $this->assertArrayHasKey('date_uploaded', $ret);
        $this->assertArrayHasKey('status', $ret);

        $this->assertInstanceOf('DateTime', $ret['date_uploaded']);
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
     * @expectedException Xi\Filelib\FilelibException
     * @dataProvider invalidFileIdProvider
     * @param mixed $fileId
     */
    public function findFileThrowsExceptionWithInvalidIdentifier($fileId)
    {
        $this->setUpEmptyDataSet();

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
            $this->assertArrayHasKey('mimetype', $ret);
            $this->assertArrayHasKey('profile', $ret);
            $this->assertArrayHasKey('size', $ret);
            $this->assertArrayHasKey('name', $ret);
            $this->assertArrayHasKey('link', $ret);
            $this->assertArrayHasKey('date_uploaded', $ret);
            $this->assertArrayHasKey('status', $ret);

            $this->assertInstanceOf('DateTime', $ret['date_uploaded']);
        }
    }

    /**
     * @test
     * @dataProvider updateFileProvider
     * @param mixed $fileId
     * @param mixed $folderId
     */
    public function updateFileShouldUpdateFile($fileId, $folderId)
    {
        $this->setUpSimpleDataSet();

        $data = array(
            'id'            => $fileId,
            'folder_id'     => $folderId,
            'mimetype'      => 'image/jpg',
            'profile'       => 'lussed',
            'size'          => '1006',
            'name'          => 'tohtori-sykero.png',
            'link'          => 'tohtori-sykero.png',
            'date_uploaded' => new DateTime('2011-01-02 16:16:16'),
            'status'        => 666,
        );

        $file = FileItem::create($data);

        $this->assertTrue($this->backend->updateFile($file));
        $this->assertEquals($data, $this->backend->findFile($fileId));
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
            'mimetype'      => 'image/jpg',
            'profile'       => 'lussed',
            'size'          => '1006',
            'name'          => 'tohtori-sykero.png',
            'link'          => 'tohtori-sykero.png',
            'date_uploaded' => new DateTime('2011-01-02 16:16:16'),
            'status'        => 4,
        );

        $file = FileItem::create($updated);

        $this->setExpectedException(
            'Xi\Filelib\FilelibException',
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

        $file = FileItem::create(array('id' => $fileId));

        $this->assertTrue($this->backend->deleteFile($file));
        $this->assertFalse($this->backend->findFile($fileId));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     * @dataProvider invalidFileIdProvider
     * @param mixed $fileId
     */
    public function deleteFileThrowsExceptionWithInvalidIdentifier($fileId)
    {
        $this->setUpEmptyDataSet();

        $file = FileItem::create(array('id' => $fileId));

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

        $file = FileItem::create(array('id' => $fileId));

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
            'mimetype'      => 'image/png',
            'profile'       => 'versioned',
            'size'          => '1000',
            'name'          => 'tohtori-tussi.png',
            'link'          => 'tohtori-tussi.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
            'status'        => 5,
        );

        $fodata = array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        );

        $file = FileItem::create($fidata);
        $folder = FolderItem::create($fodata);

        $file = $this->backend->upload($file, $folder);

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);
        $this->assertNotNull($file->getId());

        $this->assertEquals($fodata['id'], $file->getFolderId());
        $this->assertEquals($fidata['mimetype'], $file->getMimeType());
        $this->assertEquals($fidata['profile'], $file->getProfile());
        $this->assertEquals($fidata['link'], $file->getLink());
        $this->assertEquals($fidata['date_uploaded'], $file->getDateUploaded());
        $this->assertEquals($fidata['status'], $file->getStatus());
    }

    /**
     * @test
     * @dataProvider notFoundFolderIdProvider
     * @param mixed $folderId
     */
    public function fileUploadThrowsExceptionWithNotFoundFolder($folderId)
    {
        $this->setUpEmptyDataSet();

        $file = FileItem::create(array(
            'mimetype'      => 'image/png',
            'profile'       => 'versioned',
            'size'          => '1000',
            'name'          => 'tohtori-tussi.png',
            'link'          => 'tohtori-tussi.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
            'status'        => 3,
        ));

        $folder = FolderItem::create(array(
            'id'        => $folderId,
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $this->setExpectedException(
            'Xi\Filelib\FilelibException',
            sprintf('Folder was not found with id "%s"', $folderId)
        );

        $this->backend->upload($file, $folder);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     * @dataProvider folderIdProvider
     * @param mixed $folderId
     */
    public function fileUploadShouldThrowExceptionWithAlreadyExistingFile(
        $folderId
    ) {
        $this->setUpSimpleDataSet();

        $fidata = array(
            'mimetype'      => 'image/png',
            'profile'       => 'versioned',
            'size'          => '1000',
            'name'          => 'tohtori-vesala.png',
            'link'          => 'tohtori-vesala.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
            'status'        => 4,
        );

        $fodata = array(
            'id'        => $folderId,
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
     * @dataProvider findFileByFilenameProvider
     * @param mixed $fileId
     * @param mixed $folderId
     */
    public function findFileByFilenameShouldReturnCorrectFile($fileId,
        $folderId
    ) {
        $this->setUpSimpleDataSet();

        $fidata = array(
            'mimetype'      => 'image/png',
            'profile'       => 'versioned',
            'size'          => 1000,
            'name'          => 'tohtori-vesala.png',
            'link'          => 'tohtori-vesala.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
            'id'            => $fileId,
            'folder_id'     => $folderId,
            'status'        => 1,
        );

        $fodata = array(
            'id'        => $folderId,
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
     * @dataProvider folderIdProvider
     * @param mixed $folderId
     */
    public function findFileByFilenameShouldNotFindNonExistingFile($folderId)
    {
        $this->setUpSimpleDataSet();

        $folder = FolderItem::create(array(
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
     * @expectedException Xi\Filelib\FilelibException
     * @dataProvider invalidFolderIdProvider
     * @param mixed $folderId
     */
    public function findFileByFileNameThrowsExceptionWithInvalidFolderIdentifier(
        $folderId
    ) {
        $this->setUpEmptyDataSet();

        $folder = FolderItem::create(array(
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
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockAndDisableOriginalConstructor($className)
    {
        return $this->getMockBuilder($className)
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
