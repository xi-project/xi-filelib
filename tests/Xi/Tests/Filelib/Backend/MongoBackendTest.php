<?php

namespace Xi\Tests\Filelib\Backend;

use PHPUnit_Framework_TestCase;
use Xi\Filelib\Backend\MongoBackend;
use Xi\Filelib\Folder\FolderItem;
use Xi\Filelib\File\FileItem;
use DateTime;
use Mongo;
use MongoDB;
use MongoId;
use MongoDate;
use MongoConnectionException;

/**
 * @group mongo
 */
class MongoBackendTest extends AbstractBackendTest
{
    /**
     * @var @MongoDB
     */
    protected $mongo;

    /**
     * Implements AbstractBackendTest::setUpBackend
     *
     * @return MongoBackend
     */
    protected function setUpBackend()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('MongoDB extension is not loaded.');
        }

        try {
            $mongo = new Mongo(MONGO_DNS, array('connect' => true));
        } catch (MongoConnectionException $e) {
            $this->markTestSkipped('Can not connect to MongoDB.');
        }

        // TODO: Fix hard coded db name.
        $this->mongo = $mongo->filelib_tests;

        return new MongoBackend($this->mongo);
    }

    protected function tearDown()
    {
        if (extension_loaded('mongo') && $this->mongo) {
            foreach ($this->mongo->listCollections() as $collection) {
                $collection->drop();
            }
        }

        $this->mongo = null;
    }

    /**
     * Implements AbstractBackendTest::setUpEmptyDataSet
     */
    protected function setUpEmptyDataSet()
    {
        $this->setUpIndexes();
    }

    /**
     * Implements AbstractBackendTest::setUpSimpleDataSet
     */
    protected function setUpSimpleDataSet()
    {
        $this->setUpIndexes();

        foreach ($this->getData() as $coll => $objects) {
            foreach ($objects as $obj) {
                $this->mongo->$coll->insert($obj);
            }
        }
    }

    private function setUpIndexes()
    {
        $this->mongo->files->ensureIndex(array(
            'folder_id' => 1,
            'name'      => 1
        ), array('unique' => true));

        $this->mongo->folders->ensureIndex(
            array('name' => 1),
            array('unique' => true)
        );
    }

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
                    'status'        => 1,
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
                    'status'        => 2,
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
                    'status'        => 4,
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
                    'status'        => 8,
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
                    'status'        => 16,
                ),
            ),
        );

        foreach ($data['files'] as &$file) {
            $file['date_uploaded'] = new MongoDate($file['date_uploaded']->getTimeStamp());
        }

        return $data;
    }

    /**
     * @return array
     */
    public function rootFolderIdProvider()
    {
        return array(
            array('49a7011a05c677b9a9166101'),
        );
    }

    /**
     * @return array
     */
    public function findFolderProvider()
    {
        return array(
            array('49a7011a05c677b9a9166101', array('name' => 'root')),
            array('49a7011a05c677b9a9166102', array('name' => 'lussuttaja')),
            array('49a7011a05c677b9a9166103', array('name' => 'tussin')),
            array('49a7011a05c677b9a9166104', array('name' => 'banskun')),
        );
    }

    /**
     * @return array
     */
    public function filelessFolderIdProvider()
    {
        return array(
            array('49a7011a05c677b9a9166105'),
        );
    }

    /**
     * @test
     */
    public function findFolderShouldReturnNullWhenTryingToFindNonExistingFolder()
    {
        $this->assertFalse($this->backend->findFolder('49a7011a05c677b9a9166188'));
    }

    /**
     * @return array
     */
    public function parentFolderIdProvider()
    {
        return array(
            array('49a7011a05c677b9a9166103'),
        );
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
     * @return array
     */
    public function updateFolderProvider()
    {
        return array(
            array('49a7011a05c677b9a9166103', '49a7011a05c677b9a9166102', '49a7011a05c677b9a9166101')
        );
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
     * @return array
     */
    public function subFolderProvider()
    {
        return array(
            array('49a7011a05c677b9a9166101', 1),
            array('49a7011a05c677b9a9166102', 3),
            array('49a7011a05c677b9a9166104', 0),
        );
    }

    /**
     * @return array
     */
    public function folderByUrlProvider()
    {
        return array(
            array('lussuttaja/tussin', '49a7011a05c677b9a9166103'),
        );
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
     * @return array
     */
    public function findFilesInProvider()
    {
        return array(
            array('49a7011a05c677b9a9166101', 1),
            array('49a7011a05c677b9a9166104', 2),
            array('49a7011a05c677b9a9166105', 0),
        );
    }

    /**
     * @return array
     */
    public function findFileProvider()
    {
        return array(
            array('49a7011a05c677b9a9166106'),
        );
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
     * @return array
     */
    public function updateFileProvider()
    {
        return array(
            array('49a7011a05c677b9a9166106', '49a7011a05c677b9a9166102'),
        );
    }

    /**
     * @return array
     */
    public function deleteFileProvider()
    {
        return array(
            array('49a7011a05c677b9a9166110'),
        );
    }

    /**
     * @return array
     */
    public function folderIdProvider()
    {
        return array(
            array('49a7011a05c677b9a9166101'),
        );
    }

    /**
     * @return array
     */
    public function findFileByFilenameProvider()
    {
        return array(
            array('49a7011a05c677b9a9166106', '49a7011a05c677b9a9166101'),
        );
    }

    /**
     * @return array
     */
    public function invalidFolderIdProvider()
    {
        return array(
            array(1),
        );
    }

    /**
     * @return array
     */
    public function invalidFileIdProvider()
    {
        return array(
            array(1),
        );
    }

    /**
     * @return array
     */
    public function notFoundFolderIdProvider()
    {
        return array(
            array('49a7011a05c677b9a9166666'),
        );
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
}
