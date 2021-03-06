<?php

namespace Xi\Filelib\Tests\Backend\Adapter;

use Xi\Filelib\Backend\Adapter\MongoBackendAdapter;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Filelib\Backend\Finder\ResourceFinder;
use DateTime;
use MongoDB\Client;
use MongoDB\Database;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;


/**
 * @group backend
 * @group mongo
 */
class MongoBackendAdapterTest extends AbstractBackendAdapterTestCase
{
    /**
     * @var Database
     */
    protected $mongo;

    public function assertValidCreatedIdentifier($identifier)
    {
        $this->assertNotNull($identifier);
        $this->assertInternalType('string', $identifier);
    }

    /**
     * Implements AbstractBackendAdapterTest::setUpBackend
     *
     * @return MongoBackendAdapter
     */
    protected function setUpBackend()
    {
        if (!extension_loaded('mongodb')) {
            $this->markTestSkipped('MongoDB extension is not loaded.');
        }

        try {

            $mongo = new Client(MONGO_DNS, [], [
                'typeMap' => [
                    'array' => 'array',
                    'document' => 'array',
                    'root' => 'array',
                ],
            ]);
        } catch (\Exception $e) {
            return $this->markTestSkipped('Can not connect to MongoDB.');
        }

        // TODO: Fix hard coded db name.
        $this->mongo = $mongo->selectDatabase("filelib_test");

        return new MongoBackendAdapter($this->mongo);
    }

    protected function tearDown()
    {
        if (extension_loaded('mongodb') && $this->mongo) {
            foreach ($this->mongo->listCollections() as $collection) {
                $this->mongo->dropCollection($collection->getName());
            }
        }

        $this->mongo = null;

        parent::tearDown();
    }

    /**
     * Implements AbstractBackendAdapterTest::setUpEmptyDataSet
     */
    protected function setUpEmptyDataSet()
    {
        $this->setUpIndexes();
    }

    /**
     * Implements AbstractBackendAdapterTest::setUpSimpleDataSet
     */
    protected function setUpSimpleDataSet()
    {
        $this->setUpIndexes();

        foreach ($this->getData() as $coll => $objects) {
            foreach ($objects as $obj) {
                $this->mongo->$coll->insertOne($obj);
            }
        }
    }

    private function setUpIndexes()
    {
        $this->mongo->files->createIndex(
            array(
                'folder_id' => 1,
                'name'      => 1
            ),
            array('unique' => true)
        );

        $this->mongo->folders->createIndex(
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

            'resources' => array(
                array(
                    '_id' => new ObjectId('48a7011a05c677b9a9166101'),
                    'uuid' => 'ruuid-1',
                    'hash' => 'hash-1',
                    'date_created' => new DateTime('1978-03-21 06:06:06'),
                    'data' => array(
                        'versions' => array('tussi', 'watussi', 'pygmi'),
                    ),
                    'mimetype' => 'image/png',
                    'size' => 10000,
                    'exclusive' => true,
                ),
                array(
                    '_id' => new ObjectId('48a7011a05c677b9a9166102'),
                    'uuid' => 'ruuid-2',
                    'hash' => 'hash-2',
                    'date_created' => new DateTime('1988-03-21 06:06:06'),
                    'data' => array(
                        'versions' => array(),
                    ),
                    'mimetype' => 'image/png',
                    'size' => 20000,
                    'exclusive' => true,
                ),
                array(
                    '_id' => new ObjectId('48a7011a05c677b9a9166103'),
                    'uuid' => 'ruuid-3',
                    'hash' => 'hash-2',
                    'date_created' => new DateTime('1998-03-21 06:06:06'),
                    'data' => array(
                        'versions' => array('pygmi', 'tussi'),
                    ),
                    'mimetype' => 'image/png',
                    'size' => 30000,
                    'exclusive' => true,
                ),
                array(
                    '_id' => new ObjectId('48a7011a05c677b9a9166104'),
                    'uuid' => 'ruuid-4',
                    'hash' => 'hash-3',
                    'date_created' => new DateTime('2008-03-21 06:06:06'),
                    'data' => array(
                        'versions' => array('watussi'),
                    ),
                    'mimetype' => 'image/jpg',
                    'size' => 40000,
                    'exclusive' => true,
                ),
                array(
                    '_id' => new ObjectId('48a7011a05c677b9a9166105'),
                    'uuid' => 'ruuid-5',
                    'hash' => 'hash-5',
                    'date_created' => new DateTime('2009-03-21 06:06:06'),
                    'data' => array(
                        'versions' => array('watussi', 'loso'),
                    ),
                    'mimetype' => 'video/xxx',
                    'size' => 50000,
                    'exclusive' => true,
                ),
            ),
            'folders' => array(
                array(
                    '_id'       => new ObjectId('49a7011a05c677b9a9166101'),
                    'parent_id' => null,
                    'url'       => '',
                    'name'      => 'root',
                    'uuid'      => 'uuid-f-49a7011a05c677b9a9166101',
                    'data'      => array(
                        'lusso' => array(
                            'gran-tusso' => 'libaisu',
                        )
                    )
                ),
                array(
                    '_id'       => new ObjectId('49a7011a05c677b9a9166102'),
                    'parent_id' => '49a7011a05c677b9a9166101',
                    'url'       => 'lussuttaja',
                    'name'      => 'lussuttaja',
                    'uuid'      => 'uuid-f-49a7011a05c677b9a9166102',
                    'data'      => array(
                        'lusso' => array(
                            'gran-tusso' => 'libaisu',
                        )
                    )
                ),
                array(
                    '_id'       => new ObjectId('49a7011a05c677b9a9166103'),
                    'parent_id' => '49a7011a05c677b9a9166102',
                    'url'       => 'lussuttaja/tussin',
                    'name'      => 'tussin',
                    'uuid'      => 'uuid-f-49a7011a05c677b9a9166103',
                    'data'      => array(
                        'lusso' => array(
                            'gran-tusso' => 'libaisu',
                        )
                    )
                ),
                array(
                    '_id'       => new ObjectId('49a7011a05c677b9a9166104'),
                    'parent_id' => '49a7011a05c677b9a9166102',
                    'url'       => 'lussuttaja/banskun',
                    'name'      => 'banskun',
                    'uuid'      => 'uuid-f-49a7011a05c677b9a9166104',
                    'data'      => array(
                        'lusso' => array(
                            'gran-tusso' => 'libaisu',
                        )
                    )
                ),
                array(
                    '_id'       => new ObjectId('49a7011a05c677b9a9166105'),
                    'parent_id' => '49a7011a05c677b9a9166102',
                    'url'       => 'lussuttaja/tiedoton-kansio',
                    'name'      => 'tiedoton-kansio',
                    'uuid'      => 'uuid-f-49a7011a05c677b9a9166105',
                    'data'      => array(
                        'lusso' => array(
                            'gran-tusso' => 'libaisu',
                        )
                    )
                ),
            ),
            'files' => array(
                array(
                    '_id'           => new ObjectId('49a7011a05c677b9a9166106'),
                    'folder_id'     => '49a7011a05c677b9a9166101',
                    'profile'       => 'versioned',
                    'name'          => 'tohtori-vesala.png',
                    'link'          => 'tohtori-vesala.png',
                    'date_created' => new DateTime('2011-01-01 16:16:16'),
                    'status'        => 1,
                    'uuid'          => 'uuid-1',
                    'resource_id'   => '48a7011a05c677b9a9166101',
                    'data' => array(
                        'versions' => array(),
                    ),
                ),
                array(
                    '_id'           => new ObjectId('49a7011a05c677b9a9166107'),
                    'folder_id'     => '49a7011a05c677b9a9166102',
                    'profile'       => 'versioned',
                    'name'          => 'akuankka.png',
                    'link'          => 'lussuttaja/akuankka.png',
                    'date_created' => new DateTime('2011-01-01 15:15:15'),
                    'status'        => 2,
                    'uuid'          => 'uuid-2',
                    'resource_id'   => '48a7011a05c677b9a9166102',
                    'data' => array(
                        'versions' => array(),
                    ),
                ),
                array(
                    '_id'           => new ObjectId('49a7011a05c677b9a9166108'),
                    'folder_id'     => '49a7011a05c677b9a9166103',
                    'profile'       => 'default',
                    'name'          => 'repesorsa.png',
                    'link'          => 'lussuttaja/tussin/repesorsa.png',
                    'date_created' => new DateTime('2011-01-01 15:15:15'),
                    'status'        => 4,
                    'uuid'          => 'uuid-3',
                    'resource_id'   => '48a7011a05c677b9a9166103',
                    'data' => array(
                        'versions' => array(),
                    ),
                ),
                array(
                    '_id'           => new ObjectId('49a7011a05c677b9a9166109'),
                    'folder_id'     => '49a7011a05c677b9a9166104',
                    'profile'       => 'default',
                    'name'          => 'megatussi.png',
                    'link'          => 'lussuttaja/banskun/megatussi.png',
                    'date_created' => new DateTime('2011-01-02 15:15:15'),
                    'status'        => 8,
                    'uuid'          => 'uuid-4',
                    'resource_id'   => '48a7011a05c677b9a9166104',
                    'data' => array(
                        'versions' => array(),
                        'lipaiseppa' => 'ankkaa',
                    ),
                ),
                array(
                    '_id'           => new ObjectId('49a7011a05c677b9a9166110'),
                    'folder_id'     => '49a7011a05c677b9a9166104',
                    'profile'       => 'default',
                    'name'          => 'megatussi2.png',
                    'link'          => 'lussuttaja/banskun/megatussi2.png',
                    'date_created' => new DateTime('2011-01-03 15:15:15'),
                    'status'        => 16,
                    'uuid'          => 'uuid-5',
                    'resource_id'   => '48a7011a05c677b9a9166104',
                    'data' => array(
                        'versions' => array('kliussi', 'watussi'),
                    ),
                ),
            ),
        );

        foreach ($data['files'] as &$file) {
            $file['date_created'] = new UTCDateTime($file['date_created']->getTimeStamp());
        }

        foreach ($data['resources'] as &$resource) {
            $resource['date_created'] = new UtcDateTime($resource['date_created']->getTimeStamp());
        }

        return $data;
    }

    /**
     * @return array
     */
    public function dataPersistenceProvider()
    {
        return array(
            array('49a7011a05c677b9a9166109', 'lipaiseppa', 'ankkaa')
        );
    }

    /**
     * @return array
     */
    public function provideFinders()
    {
        return array(
            array(5, new FileFinder()),
            array(0, new FileFinder(array('id' => '49a7011a05c677b9a91661dd'))),
            array(1, new FileFinder(array('folder_id' => '49a7011a05c677b9a9166101'))),
            array(2, new FileFinder(array('folder_id' => '49a7011a05c677b9a9166104'))),
            array(
                1,
                new FileFinder(
                    array('folder_id' => '49a7011a05c677b9a9166104', 'id' => '49a7011a05c677b9a9166110')
                )
            ),
            array(
                0,
                new FileFinder(
                    array('folder_id' => '49a7011a05c677b9a9166104', 'id' => '49a7011a05c677b9a916611a')
                )
            ),
            array(0, new FileFinder(array('folder_id' => '49a7011a05c677b9a9166103', 'name' => 'repesorsa.lus'))),
            array(1, new FileFinder(array('folder_id' => '49a7011a05c677b9a9166103', 'name' => 'repesorsa.png'))),
            array(1, new FolderFinder(array('url' => 'lussuttaja/tussin'))),
            array(0, new FolderFinder(array('url' => 'lussuttaja/ankan'))),
            array(1, new FolderFinder(array('parent_id' => null))),
            array(3, new FolderFinder(array('parent_id' => '49a7011a05c677b9a9166102'))),
            array(0, new ResourceFinder(array('hash' => 'unexisting-hash'))),
            array(1, new ResourceFinder(array('hash' => 'hash-1'))),
            array(2, new ResourceFinder(array('hash' => 'hash-2'))),
        );
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
     * @return array
     */
    public function nonExistingFolderIdProvider()
    {
        return array(
            array('49a7011a05c677b9a9166188'),
        );
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
     * @return array
     */
    public function updateFolderProvider()
    {
        return array(
            array('49a7011a05c677b9a9166103', '49a7011a05c677b9a9166102', '49a7011a05c677b9a9166101')
        );
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
    public function folderIdWithFilesProvider()
    {
        return array(
            array('49a7011a05c677b9a9166101'),
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
     * @return array
     */
    public function updateFileProvider()
    {
        return array(
            array('49a7011a05c677b9a9166106', '49a7011a05c677b9a9166102', '48a7011a05c677b9a9166102'),
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
            array('49a7011a05c677b9a9166106', '49a7011a05c677b9a9166101', '48a7011a05c677b9a9166101'),
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

    public function nonExistingResourceIdProvider()
    {
        return array(
            array('48a7011a05c677b9a9166166'),
            array('48a7011a05c677b9a9166177'),
            array('48a7011a05c677b9a9166188'),
        );
    }

    /**
     * @return array
     */
    public function referenceCountProvider()
    {
        return array(
            array(1, '48a7011a05c677b9a9166101'),
            array(1, '48a7011a05c677b9a9166102'),
            array(1, '48a7011a05c677b9a9166103'),
            array(2, '48a7011a05c677b9a9166104'),
        );
    }

    /**
     * @return array
     */
    public function resourceHashProvider()
    {
        return array(
            array('hash-1', 1),
            array('hash-2', 2),
            array('hash-3', 1),
            array('hash-4', 0),
            array('hash-666', 0),
        );
    }

    /**
     * @return array
     */
    public function orphanResourceIdProvider()
    {
        return array(
            array('48a7011a05c677b9a9166105'),
        );
    }

    /**
     * @return array
     */
    public function resourceIdWithReferencesProvider()
    {
        return array(
            array('48a7011a05c677b9a9166103'),
        );
    }

    /**
     * @return array
     */
    public function findResourceProvider()
    {
        return array(
            array(
                '48a7011a05c677b9a9166101',
                array('hash' => 'hash-1', 'versions' => array('tussi', 'watussi', 'pygmi'))
            ),
            array('48a7011a05c677b9a9166102', array('hash' => 'hash-2', 'versions' => array())),
            array('48a7011a05c677b9a9166103', array('hash' => 'hash-2', 'versions' => array('pygmi', 'tussi'))),
            array('48a7011a05c677b9a9166104', array('hash' => 'hash-3', 'versions' => array('watussi'))),
        );
    }

    /**
     *
     * @return array
     */
    public function updateResourceProvider()
    {
        return array(
            array(
                '48a7011a05c677b9a9166101', array('imaisebba', 'tussia'),
            )
        );
    }
}
