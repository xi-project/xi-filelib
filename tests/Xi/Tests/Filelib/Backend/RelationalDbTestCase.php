<?php

namespace Xi\Tests\Filelib\Backend;

use PDO,
    PDOException,
    PHPUnit_Extensions_Database_TestCase,
    PHPUnit_Extensions_Database_DB_IDatabaseConnection,
    PHPUnit_Extensions_Database_Operation_Factory,
    PHPUnit_Extensions_Database_Operation_Composite,
    PHPUnit_Extensions_Database_Operation_IDatabaseOperation,
    PHPUnit_Extensions_Database_DB_MetaData_MySQL,
    PHPUnit_Extensions_Database_DataSet_AbstractDataSet,
    PHPUnit_Extensions_Database_DataSet_DefaultDataSet,
    Exception,
    DateTime,
    Xi\Tests\PHPUnit\Extensions\Database\Operation\MySQL55Truncate,
    Xi\Filelib\File\FileItem,
    Xi\Filelib\Folder\FolderItem;

/**
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 */
abstract class RelationalDbTestCase extends PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var Backend
     */
    protected $backend;

    /**
     * @var string|null
     */
    private $dataSet;

    /**
     * @return Backend
     */
    protected abstract function setUpBackend();

    protected function setUp()
    {
        // Do not call parent setUp, because we wan't to use different data sets
        // per test.

        $this->backend = $this->setUpBackend();
    }

    /**
     * @throws Exception If no data set was used.
     */
    protected function tearDown()
    {
        // Do not call parent tearDown.

        $this->getDatabaseTester()->setTearDownOperation($this->getTearDownOperation());
        $this->getDatabaseTester()->setDataSet($this->getDataSet($this->dataSet));
        $this->getDatabaseTester()->onTearDown();

        // Destroy the tester after the test is run to keep DB connections
        // from piling up.
        $this->databaseTester = null;

        $this->dataSet = null;
    }

    /**
     * @param  string                                              $dataSet
     * @return PHPUnit_Extensions_Database_DataSet_AbstractDataSet
     * @throws Exception
     */
    public function getDataSet($dataSet = null)
    {
        if ($dataSet === 'empty') {
            return $this->getEmptyDataSet();
        } else if ($dataSet === 'simple') {
            return $this->getSimpleDataSet();
        }

        throw new Exception('Please specify a data set to be used.');
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_DefaultDataSet
     */
    private function getEmptyDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
    }

    /**
     * @return ArrayDataSet
     */
    private function getSimpleDataSet()
    {
        return new ArrayDataSet(array(
            'xi_filelib_folder' => array(
                array(
                    'id'         => 1,
                    'parent_id'  => null,
                    'folderurl'  => '',
                    'foldername' => 'root',
                ),
                array(
                    'id'         => 2,
                    'parent_id'  => 1,
                    'folderurl'  => 'lussuttaja',
                    'foldername' => 'lussuttaja',
                ),
                array(
                    'id'         => 3,
                    'parent_id'  => 2,
                    'folderurl'  => 'lussuttaja/tussin',
                    'foldername' => 'tussin',
                ),
                array(
                    'id'         => 4,
                    'parent_id'  => 2,
                    'folderurl'  => 'lussuttaja/banskun',
                    'foldername' => 'banskun',
                ),
                array(
                    'id'         => 5,
                    'parent_id'  => 2,
                    'folderurl'  => 'lussuttaja/tiedoton-kansio',
                    'foldername' => 'tiedoton-kansio',
                ),
            ),
            'xi_filelib_file' => array(
                array(
                    'id'            => 1,
                    'folder_id'     => 1,
                    'mimetype'      => 'image/png',
                    'fileprofile'   => 'versioned',
                    'filesize'      => '1000',
                    'filename'      => 'tohtori-vesala.png',
                    'filelink'      => 'tohtori-vesala.png',
                    'date_uploaded' => '2011-01-01 16:16:16',
                ),
                array(
                    'id'            => 2,
                    'folder_id'     => 2,
                    'mimetype'      => 'image/png',
                    'fileprofile'   => 'versioned',
                    'filesize'      => '10001',
                    'filename'      => 'akuankka.png',
                    'filelink'      => 'lussuttaja/akuankka.png',
                    'date_uploaded' => '2011-01-01 15:15:15',
                ),
                array(
                    'id'            => 3,
                    'folder_id'     => 3,
                    'mimetype'      => 'image/png',
                    'fileprofile'   => 'default',
                    'filesize'      => '10000',
                    'filename'      => 'repesorsa.png',
                    'filelink'      => 'lussuttaja/tussin/repesorsa.png',
                    'date_uploaded' => '2011-01-01 15:15:15',
                ),
                array(
                    'id'            => 4,
                    'folder_id'     => 4,
                    'mimetype'      => 'image/png',
                    'fileprofile'   => 'default',
                    'filesize'      => '10000',
                    'filename'      => 'megatussi.png',
                    'filelink'      => 'lussuttaja/banskun/megatussi.png',
                    'date_uploaded' => '2011-01-02 15:15:15',
                ),
                array(
                    'id'            => 5,
                    'folder_id'     => 4,
                    'mimetype'      => 'image/png',
                    'fileprofile'   => 'default',
                    'filesize'      => '10000',
                    'filename'      => 'megatussi2.png',
                    'filelink'      => 'lussuttaja/banskun/megatussi2.png',
                    'date_uploaded' => '2011-01-03 15:15:15',
                ),
            ),
        ));
    }

    /**
     * Set up a test using an empty data set.
     */
    protected function setUpEmptyDataSet()
    {
        $this->setUpDataSet('empty');
    }

    /**
     * Set up a test using a simple data set.
     */
    protected function setUpSimpleDataSet()
    {
        $this->setUpDataSet('simple');
    }

    /**
     * @param string $dataSet
     */
    private function setUpDataSet($dataSet)
    {
        $this->dataSet = $dataSet;

        $this->databaseTester = null;

        $this->getDatabaseTester()->setSetUpOperation($this->getSetUpOperation());
        $this->getDatabaseTester()->setDataSet($this->getDataSet($dataSet));
        $this->getDatabaseTester()->onSetUp();
    }

    /**
     * @test
     */
    public function findRootFolderShouldReturnRootFolder()
    {
        $this->setUpSimpleDataSet();

        $rootFolder = $this->backend->findFolder(1);

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
     * @dataProvider provideForFindFolder
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
    public function findFolderShouldReturnNullWhenTryingToFindNonExistingFolder()
    {
        $this->setUpEmptyDataSet();

        $this->assertFalse($this->backend->findFolder(900));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findFolderShouldThrowExceptionWhenTryingToFindErroneousFolder()
    {
        $this->setUpEmptyDataSet();

        $this->backend->findFolder('xoo');
    }

    /**
     * @test
     */
    public function createFolderShouldCreateFolder()
    {
        $this->setUpSimpleDataSet();

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
        $this->setUpEmptyDataSet();

        $data = array(
            'parent_id' => 666,
            'name'      => 'lusander',
            'url'       => 'lussuttaja/tussin/lusander',
        );

        $this->backend->createFolder(FolderItem::create($data));
    }

    /**
     * @test
     */
    public function deleteFolderShouldDeleteFolder()
    {
        $this->setUpSimpleDataSet();

        $data = array(
            'id'        => 5,
            'parent_id' => null,
            'name'      => 'klus',
        );

        $folder = FolderItem::create($data);

        $this->assertInternalType('array', $this->backend->findFolder(5));

        $this->assertTrue($this->backend->deleteFolder($folder));

        $this->assertFalse($this->backend->findFolder(5));
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
     */
    public function deleteFolderShouldNotDeleteNonExistingFolder()
    {
        $this->setUpEmptyDataSet();

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
        $this->setUpSimpleDataSet();

        $data = array(
            'id'        => 3,
            'parent_id' => 2,
            'url'       => 'lussuttaja/tussin',
            'name'      => 'tussin',
        );

        $this->assertEquals($data, $this->backend->findFolder(3));

        $updateData = array(
            'id'        => 3,
            'parent_id' => 1,
            'url'       => 'lussuttaja/lussander',
            'name'      => 'lussander',
        );

        $folder = FolderItem::create($updateData);

        $this->assertTrue($this->backend->updateFolder($folder));
        $this->assertEquals($updateData, $this->backend->findFolder(3));
    }

    /**
     * @test
     */
    public function updatesRootFolder()
    {
        $this->setUpSimpleDataSet();

        $data = array(
            'id'        => 3,
            'parent_id' => null,
            'url'       => 'foo/bar',
            'name'      => 'xoo',
        );

        $folder = FolderItem::create($data);

        $this->assertTrue($this->backend->updateFolder($folder));
        $this->assertEquals($data, $this->backend->findFolder(3));
    }

    /**
     * @test
     */
    public function updateFolderShouldNotUpdateNonExistingFolder()
    {
        $this->setUpEmptyDataSet();

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
        $this->setUpEmptyDataSet();

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
        $this->setUpSimpleDataSet();

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
        $this->setUpEmptyDataSet();

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
        $this->setUpSimpleDataSet();

        $ret = $this->backend->findFolderByUrl('lussuttaja/tussin');

        $this->assertInternalType('array', $ret);
        $this->assertEquals(3, $ret['id']);
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
     */
    public function findFilesInShouldReturnArrayOfFiles()
    {
        $this->setUpSimpleDataSet();

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
        $this->setUpEmptyDataSet();

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
        $this->setUpSimpleDataSet();

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
     */
    public function findFileReturnsFalseIfFileIsNotFound()
    {
        $this->setUpEmptyDataSet();

        $this->assertFalse($this->backend->findFile(1));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findFileShouldThrowExceptionWithErroneousId()
    {
        $this->setUpEmptyDataSet();

        $this->backend->findFile('xooxoeroe');
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

            $this->assertInstanceOf('DateTime', $ret['date_uploaded']);
        }
    }

    /**
     * @test
     */
    public function updateFileShouldUpdateFile()
    {
        $this->setUpSimpleDataSet();

        $data = array(
            'id'            => 1,
            'folder_id'     => 2,
            'mimetype'      => 'image/jpg',
            'profile'       => 'lussed',
            'size'          => '1006',
            'name'          => 'tohtori-sykero.png',
            'link'          => 'tohtori-sykero.png',
            'date_uploaded' => new DateTime('2011-01-02 16:16:16'),
        );

        $file = FileItem::create($data);

        $this->assertTrue($this->backend->updateFile($file));
        $this->assertEquals($data, $this->backend->findFile(1));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function updateFileShouldThrowExceptionWithErroneousFile()
    {
        $this->setUpSimpleDataSet();

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
        $this->setUpSimpleDataSet();

        $file = FileItem::create(array('id' => 5));

        $this->assertTrue($this->backend->deleteFile($file));
        $this->assertFalse($this->backend->findFile(5));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function deleteFileShouldThrowExceptionWithErroneousFile()
    {
        $this->setUpEmptyDataSet();

        $file = FileItem::create(array('id' => 'xooxoox'));

        $this->backend->deleteFile($file);
    }

    /**
     * @test
     */
    public function deleteFileReturnsFalseIfFileIsNotFound()
    {
        $this->setUpEmptyDataSet();

        $file = FileItem::create(array('id' => 1));

        $this->assertFalse($this->backend->deleteFile($file));
    }

    /**
     * @test
     */
    public function fileUploadShouldUploadFile()
    {
        $this->setUpSimpleDataSet();

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
        $this->setUpEmptyDataSet();

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
        $this->setUpSimpleDataSet();

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
        $this->setUpSimpleDataSet();

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
        $this->setUpSimpleDataSet();

        $folder = FolderItem::create(array(
            'id'        => 1,
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
    public function findFileByFileNameShouldThrowExceptionWithErroneousFolder()
    {
        $this->setUpEmptyDataSet();

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
     */
    public function getsAndSetsFilelib()
    {
        $this->setUpEmptyDataSet();

        $filelib = $this->getMockAndDisableOriginalConstructor(
            'Xi\Filelib\FileLibrary'
        );

        $this->assertNotSame($filelib, $this->backend->getFilelib());

        $this->backend->setFilelib($filelib);

        $this->assertSame($filelib, $this->backend->getFilelib());
    }

    /**
     * @test
     */
    public function initCanBeCalled()
    {
        $this->setUpEmptyDataSet();

        $this->backend->init();
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

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $dsn = sprintf('%s:host=%s;dbname=%s', PDO_DRIVER, PDO_HOST, PDO_DBNAME);

        try {
            $pdo = new PDO($dsn, PDO_USERNAME, PDO_PASSWORD);
        } catch (PDOException $e) {
            $this->markTestSkipped('Could not connect to database.');
        }

        return $this->createDefaultDBConnection($pdo);
    }

    /**
     * @return PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    protected function getSetUpOperation()
    {
        if ($this->isMySQL()) {
            return new PHPUnit_Extensions_Database_Operation_Composite(array(
                new MySQL55Truncate(true),
                PHPUnit_Extensions_Database_Operation_Factory::INSERT()
            ));
        }

        return PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT(true);
    }

    /**
     * @return PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    protected function getTearDownOperation()
    {
        if ($this->isMySQL()) {
            return new PHPUnit_Extensions_Database_Operation_Composite(array(
                new MySQL55Truncate(true)
            ));
        }

        return PHPUnit_Extensions_Database_Operation_Factory::DELETE_ALL();
    }

    /**
     * @return boolean
     */
    private function isMySQL()
    {
        return $this->getConnection()->getMetaData() instanceof PHPUnit_Extensions_Database_DB_MetaData_MySQL;
    }
}
