<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage\Adapter;

use MongoClient;
use MongoDB;
use MongoConnectionException;
use Xi\Filelib\Storage\Adapter\GridfsStorageAdapter;

/**
 * @group storage
 */
class GridFsStorageAdapterTest extends TestCase
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
        $storage = new GridfsStorageAdapter($this->mongo);

        return array($storage, true);
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
        list ($storage,) = $this->getStorage();
        $this->assertSame('xi_filelib', $storage->getPrefix());
    }
}
