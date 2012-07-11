<?php

namespace Xi\Tests\Filelib\Backend;

use PHPUnit_Framework_TestCase;
use DateTime;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\File\File;
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

    protected function tearDown()
    {
        // Unset to keep database connections from piling up.
        $this->backend = null;

        // Collect garbages manually to free up connections.
        gc_collect_cycles();
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
        );

        $this->assertEquals($data, $this->backend->findFolder($folderId));

        $updateData = array(
            'id'        => $folderId,
            'parent_id' => $updatedParentFolderId,
            'url'       => 'lussuttaja/lussander',
            'name'      => 'lussander',
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

        $file = File::create($data);

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
            'mimetype'      => 'image/png',
            'profile'       => 'versioned',
            'size'          => 1000,
            'name'          => 'tohtori-tussi.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
            'status'        => 5,
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

        $fileDataAfterUpload = array_merge($fidata, array(
            'id'        => $file->getId(),
            'folder_id' => $file->getFolderId(),
            'link'      => null,
        ));

        $this->assertEquals($fileDataAfterUpload, $file->toArray());
        $this->assertEquals($fileDataAfterUpload, $this->backend->findFile($file->getId()));
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
            'mimetype'      => 'image/png',
            'profile'       => 'versioned',
            'size'          => '1000',
            'name'          => 'tohtori-tussi.png',
            'link'          => 'tohtori-tussi.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
            'status'        => 3,
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
            'mimetype'      => 'image/png',
            'profile'       => 'versioned',
            'size'          => '1000',
            'name'          => 'tohtori-vesala.png',
            'link'          => 'tohtori-vesala.png',
            'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
            'status'        => 4,
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

        $folder = Folder::create($fodata);

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
            'Xi\Filelib\Exception\InvalidArgumentException',
            sprintf(
                'File id must be %s, %s (%s) given',
                $validType,
                gettype($fileId),
                $fileId
            )
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
            'Xi\Filelib\Exception\InvalidArgumentException',
            sprintf(
                'Folder id must be %s, %s (%s) given',
                $validType,
                gettype($folderId),
                $folderId
            )
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
