<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage;

use Aws\S3\S3Client;
use Xi\Filelib\Storage\GaufretteStorage;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator;
use Gaufrette\Filesystem;

use Gaufrette\Adapter\Local as LocalAdapter;
use Gaufrette\Adapter\AwsS3 as AwsAdapter;

/**
 * @group storage
 */
class GaufretteStorageTest extends TestCase
{
    /**
     * @return Filesystem
     */
    private function getFilesystem()
    {
        /*
        $config = array(
            'key'    => S3_KEY,
            'secret' => S3_SECRETKEY
        );

        $client = S3Client::factory($config);
        $adapter = new AwsAdapter($client, S3_BUCKET);
        */

        $adapter = new LocalAdapter(ROOT_TESTS . '/data/files');
        $filesystem = new Filesystem($adapter);

        return $filesystem;
    }

    protected function tearDown()
    {
        parent::tearDown();

        $filesystem = $this->getFilesystem();
        foreach ($filesystem->keys() as $key) {

            if (!preg_match('#\.gitignore#', $key)) {
                $filesystem->delete($key);
            }
        }
    }

    protected function getStorage()
    {
        $filesystem = $this->getFilesystem();

        $dc = new TimeDirectoryIdCalculator();
        $storage = new GaufretteStorage($filesystem, $dc);

        return $storage;
    }
}
