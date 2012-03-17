<?php

namespace Xi\Tests\Filelib\Backend;

use PHPUnit_Framework_TestCase,
    Xi\Filelib\Backend\MongoBackend,
    Xi\Filelib\Folder\FolderItem,
    Xi\Filelib\File\FileItem,
    DateTime,
    Mongo,
    MongoDB,
    MongoId,
    MongoDate,
    MongoConnectionException;

/**
 * @group mongo
 */
class MongoBackendTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MongoBackend
     */
    protected $backend;

    /**
     * @var @MongoDB
     */
    protected $mongo;

    /**
     * @return array
     */
    private function getData()
    {
        $data = array(
            'folders' => array(
                array(
                    '_id'       => new MongoId('49a7011a05c677b9a9166101'),
                    'parent_id' => null,
                    'url'       => '',
                    'name'      => 'root',
                ),
                array(
                    '_id'       => new MongoId('49a7011a05c677b9a9166102'),
                    'parent_id' => '49a7011a05c677b9a9166101',
                    'url'       => 'lussuttaja',
                    'name'      => 'lussuttaja',
                ),
                array(
                    '_id'       => new MongoId('49a7011a05c677b9a9166103'),
                    'parent_id' => '49a7011a05c677b9a9166102',
                    'url'       => 'lussuttaja/tussin',
                    'name'      => 'tussin',
                ),
                array(
                    '_id'       => new MongoId('49a7011a05c677b9a9166104'),
                    'parent_id' => '49a7011a05c677b9a9166102',
                    'url'       => 'lussuttaja/banskun',
                    'name'      => 'banskun',
                ),
                array(
                    '_id'       => new MongoId('49a7011a05c677b9a9166105'),
                    'parent_id' => '49a7011a05c677b9a9166102',
                    'url'       => 'lussuttaja/tiedoton-kansio',
                    'name'      => 'tiedoton-kansio',
                ),
            ),
            'files' => array(
                array(
                    '_id'           => new MongoId('49a7011a05c677b9a9166106'),
                    'folder_id'     => '49a7011a05c677b9a9166101',
                    'mimetype'      => 'image/png',
                    'profile'       => 'versioned',
                    'size'          => '1000',
                    'name'          => 'tohtori-vesala.png',
                    'link'          => 'tohtori-vesala.png',
                    'date_uploaded' => new DateTime('2011-01-01 16:16:16'),
                ),
                array(
                    '_id'           => new MongoId('49a7011a05c677b9a9166107'),
                    'folder_id'     => '49a7011a05c677b9a9166102',
                    'mimetype'      => 'image/png',
                    'profile'       => 'versioned',
                    'size'          => '10001',
                    'name'          => 'akuankka.png',
                    'link'          => 'lussuttaja/akuankka.png',
                    'date_uploaded' => new DateTime('2011-01-01 15:15:15'),
                ),
                array(
                    '_id'           => new MongoId('49a7011a05c677b9a9166108'),
                    'folder_id'     => '49a7011a05c677b9a9166103',
                    'mimetype'      => 'image/png',
                    'profile'       => 'default',
                    'size'          => '10000',
                    'name'          => 'repesorsa.png',
                    'link'          => 'lussuttaja/tussin/repesorsa.png',
                    'date_uploaded' => new DateTime('2011-01-01 15:15:15'),
                ),
                array(
                    '_id'           => new MongoId('49a7011a05c677b9a9166109'),
                    'folder_id'     => '49a7011a05c677b9a9166104',
                    'mimetype'      => 'image/png',
                    'profile'       => 'default',
                    'size'          => '10000',
                    'name'          => 'megatussi.png',
                    'link'          => 'lussuttaja/banskun/megatussi.png',
                    'date_uploaded' => new DateTime('2011-01-02 15:15:15'),
                ),
                array(
                    '_id'           => new MongoId('49a7011a05c677b9a9166110'),
                    'folder_id'     => '49a7011a05c677b9a9166104',
                    'mimetype'      => 'image/png',
                    'profile'       => 'default',
                    'size'          => '10000',
                    'name'          => 'megatussi2.png',
                    'link'          => 'lussuttaja/banskun/megatussi2.png',
                    'date_uploaded' => new DateTime('2011-01-03 15:15:15'),
                ),
            ),
        );

        foreach ($data['files'] as &$file) {
            $file['date_uploaded'] = new MongoDate($file['date_uploaded']->getTimeStamp());
        }

        return $data;
    }

    public function setUp()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('MongoDB extension is not loaded.');
        }

        try {
            $mongo = new Mongo(MONGO_DNS, array('connect' => true));
        } catch (MongoConnectionException $e) {
            $this->markTestSkipped('Can not connect to MongoDB.');
        }

        $this->mongo = $mongo->filelib_tests;

        $this->mongo->files->ensureIndex(array(
            'folder_id' => 1,
            'name'      => 1
        ), array('unique' => true));

        $this->mongo->folders->ensureIndex(
            array('name' => 1),
            array('unique' => true)
        );

        $this->backend = new MongoBackend($this->mongo);

        foreach ($this->getData() as $coll => $objects) {
            foreach ($objects as $obj) {
                $this->mongo->$coll->insert($obj);
            }
        }
    }

    protected function tearDown()
    {
        if (extension_loaded('mongo') && $this->mongo) {
            foreach ($this->mongo->listCollections() as $collection) {
                $collection->drop();
            }
        }
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
            array('49a7011a05c677b9a9166101', array('name' => 'root')),
            array('49a7011a05c677b9a9166102', array('name' => 'lussuttaja')),
            array('49a7011a05c677b9a9166103', array('name' => 'tussin')),
            array('49a7011a05c677b9a9166104', array('name' => 'banskun')),
        );
    }

    /**
     * @test
     * @dataProvider provideForFindFolder
     */
    public function findFolderShouldReturnCorrectFolder($folderId, $data)
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
        $this->assertFalse($this->backend->findFolder('49a7011a05c677b9a9166188'));
    }

    /**
     * @test
     */
    public function createFolderShouldCreateFolder()
    {
        $data = array(
            'parent_id' => '49a7011a05c677b9a9166103',
            'name'      => 'lusander',
            'url'       => 'lussuttaja/tussin/lusander',
        );

        $folder = FolderItem::create($data);

        $this->assertNull($folder->getId());

        $ret = $this->backend->createFolder($folder);

        $this->assertInternalType('string', $ret->getId());
    }

    /**
     * @test
     */
    public function deleteFolderShouldDeleteFolder()
    {
        $data = array(
            'id'        => '49a7011a05c677b9a9166105',
            'parent_id' => null,
            'name'      => 'klus',
        );

        $folder = FolderItem::create($data);

        $this->assertInternalType('array', $this->backend->findFolder('49a7011a05c677b9a9166105'));

        $this->assertTrue($this->backend->deleteFolder($folder));

        $this->assertFalse($this->backend->findFolder('49a7011a05c677b9a9166105'));
    }

    /**
     * @test
     */
    public function deleteFolderShouldNotDeleteNonExistingFolder()
    {
        $data = array(
            'id'        => '49a7011a05c677b9a9166100',
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
            'id'        => '49a7011a05c677b9a9166103',
            'parent_id' => '49a7011a05c677b9a9166102',
            'url'       => 'lussuttaja/tussin',
            'name'      => 'tussin',
        );

        $this->assertEquals($data, $this->backend->findFolder('49a7011a05c677b9a9166103'));

        $updateData = array(
            'id'        => '49a7011a05c677b9a9166103',
            'parent_id' => '49a7011a05c677b9a9166101',
            'url'       => 'lussuttaja/lussander',
            'name'      => 'lussander',
        );

        $folder = FolderItem::create($updateData);

        $this->assertTrue($this->backend->updateFolder($folder));
        $this->assertEquals($updateData, $this->backend->findFolder('49a7011a05c677b9a9166103'));
    }

    /**
     * @test
     */
    public function updateFolderShouldNotUpdateNonExistingFolder()
    {
        $folder = FolderItem::create(array(
            'id'        => '49a7011a05c677b9a9166166',
            'parent_id' => 1,
            'url'       => 'lussuttaja/lussander',
            'name'      => 'lussander',
        ));

        $this->assertFalse($this->backend->updateFolder($folder));
    }

    /**
     * @test
     */
    public function findSubFoldersShouldReturnArrayOfSubFolders()
    {
        $folder = FolderItem::create(array(
            'id'        => '49a7011a05c677b9a9166101',
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findSubFolders($folder);

        $this->assertInternalType('array', $ret);
        $this->assertCount(1, $ret);

        $folder = FolderItem::create(array(
            'id'        => '49a7011a05c677b9a9166102',
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findSubFolders($folder);

        $this->assertInternalType('array', $ret);
        $this->assertCount(3, $ret);

        $folder = FolderItem::create(array(
            'id'        => '49a7011a05c677b9a9166104',
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
     */
    public function findFolderByUrlShouldReturnFolder()
    {
        $ret = $this->backend->findFolderByUrl('lussuttaja/tussin');

        $this->assertInternalType('array', $ret);
        $this->assertEquals('49a7011a05c677b9a9166103', $ret['id']);
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
            'id'        => '49a7011a05c677b9a9166101',
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findFilesIn($folder);

        $this->assertInternalType('array', $ret);
        $this->assertCount(1, $ret);

        $folder = FolderItem::create(array(
            'id'        => '49a7011a05c677b9a9166104',
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        ));

        $ret = $this->backend->findFilesIn($folder);

        $this->assertInternalType('array', $ret);
        $this->assertCount(2, $ret);

        $folder = FolderItem::create(array(
            'id'        => '49a7011a05c677b9a9166105',
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
     */
    public function findFileShouldReturnFile()
    {
        $ret = $this->backend->findFile('49a7011a05c677b9a9166106');

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
    public function findFileShouldFailWhenIdentifierIsInvalid()
    {
        $ret = $this->backend->findFile(155);
    }
        
    
    /**
     * @test
     */
    public function findFileReturnsFalseIfFileIsNotFound()
    {
        $this->assertFalse(
            $this->backend->findFile('49a7011a05c677b9a9166156')
        );
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
        $data = array(
            'id'            => '49a7011a05c677b9a9166106',
            'folder_id'     => '49a7011a05c677b9a9166102',
            'mimetype'      => 'image/jpg',
            'profile'       => 'lussed',
            'size'          => 1006,
            'name'          => 'tohtori-sykero.png',
            'link'          => 'tohtori-sykero.png',
            'date_uploaded' => new DateTime('2011-01-02 16:16:16'),
        );

        $file = FileItem::create($data);

        $this->assertTrue($this->backend->updateFile($file));
        $this->assertEquals($data, $this->backend->findFile('49a7011a05c677b9a9166106'));
    }

    /**
     * @test
     */
    public function deleteFileShouldDeleteFile()
    {
        $file = FileItem::create(array('id' => '49a7011a05c677b9a9166110'));

        $this->assertTrue($this->backend->deleteFile($file));
        $this->assertFalse(
            $this->backend->findFile('49a7011a05c677b9a9166110')
        );
    }

    /**
     * @test
     */
    public function deleteFileReturnsFalseIfFileIsNotFound()
    {
        $file = FileItem::create(array('id' => '49a7011a05c677b9a9166100'));

        $this->assertFalse($this->backend->deleteFile($file));
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
            'id'        => '49a7011a05c677b9a9166101',
            'parent_id' => null,
            'url'       => '',
            'name'      => '',
        );

        $file = FileItem::create($fidata);
        $folder = FolderItem::create($fodata);

        $file = $this->backend->upload($file, $folder);

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);
        $this->assertInternalType('string', $file->getId());

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
            'id'        => '49a7011a05c677b9a9166101',
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
            'id'            => '49a7011a05c677b9a9166106',
            'folder_id'     => '49a7011a05c677b9a9166101',
        );

        $fodata = array(
            'id'        => '49a7011a05c677b9a9166101',
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
        $folder = FolderItem::create(array(
            'id'        => '49a7011a05c677b9a9166101',
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
    public function findRootFolderShouldCreateRootFolderIfItDoesNotExist()
    {
        $this->mongo->folders->remove(array(), array('safe' => true));
        
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
    public function initCanBeCalled()
    {
        $this->backend->init();
    }
}
