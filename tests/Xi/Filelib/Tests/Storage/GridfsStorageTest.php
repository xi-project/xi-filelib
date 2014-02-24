<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage;

use MongoClient;
use MongoDB;
use MongoConnectionException;
use Xi\Filelib\Storage\GridfsStorage;

/**
 * @group storage
 */
class GridFsStorageTest extends TestCase
{
    /**
     * @var MongoDB
     */
    protected $mongo;

    protected function getStorage()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('MongoDB extension is not loaded.');
        }

        try {
            $mongo = new MongoClient(MONGO_DNS);
        } catch (MongoConnectionException $e) {
            return $this->markTestSkipped('Can not connect to MongoDB.');
        }

        $this->mongo = $mongo->filelib_tests;
        $storage = new GridfsStorage($this->mongo, ROOT_TESTS . '/data/temp');

        return $storage;
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
    public function defaultsShouldProvideSaneStorage()
    {
        $storage = new GridfsStorage($this->getMockBuilder('MongoDB')->disableOriginalConstructor()->getMock());
        $this->assertSame('xi_filelib', $storage->getPrefix());
    }

    /**
     * @test
     */
    public function destructorShouldDeleteRetrievedFile()
    {
        $this->storage->store($this->resource, $this->resourcePath);

        $file = $this->storage->getGridFs()->findOne(array(
            'filename' => 1,
        ));

        $this->assertInstanceOf('\\MongoGridFSFile', $file);

        $retrieved = $this->storage->retrieve($this->resource);

        $this->assertFileExists($retrieved);

        unset($this->storage);

        $this->assertFileNotExists($retrieved);
    }

}
