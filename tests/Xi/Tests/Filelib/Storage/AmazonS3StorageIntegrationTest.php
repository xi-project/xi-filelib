<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Storage;

use Xi\Filelib\Storage\AmazonS3Storage;
use ZendService\Amazon\S3\S3 as AmazonService;

/**
 * @group storage
 */
class AmazonS3StorageIntegrationTest extends TestCase
{

    public function tearDown()
    {
        if (!class_exists('ZendService\Amazon\S3\S3') || !S3_KEY) {
            return $this->markTestSkipped("ZendService\Amazon\S3\S3 not found or configured");
        }

        $this->amazonService->cleanBucket(S3_BUCKET);
        parent::tearDown();
    }

    public function getStorage()
    {
        $this->amazonService = new AmazonService(S3_KEY, S3_SECRETKEY);
        $storage = new AmazonS3Storage($this->amazonService, ROOT_TESTS . '/data/temp', S3_BUCKET);
        return $storage;
    }

    public function setUp()
    {
        if (!class_exists('ZendService\Amazon\S3\S3') || !S3_KEY) {
            return $this->markTestSkipped("Zend\Service\Amazon\S3\S3 not found or configured");
        }

        parent::setUp();
    }

}
