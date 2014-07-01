<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Adapter;

use Aws\S3\Enum\CannedAcl;
use Aws\S3\S3Client;
use Guzzle\Service\Resource\Model;
use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Publisher\Linker;
use Xi\Filelib\Publisher\PublisherAdapter;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Version;

class AmazonS3PublisherAdapter implements PublisherAdapter
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var S3Client
     */
    private $client;

    /**
     * @var string
     */
    private $bucket;

    public function __construct(
        $key,
        $secretKey,
        $bucket
    ) {
        $this->bucket = $bucket;
        $this->client = S3Client::factory(
            array(
                'key'    => $key,
                'secret' => $secretKey
            )
        );
    }

    /**
     * @return S3Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->storage = $filelib->getStorage();
    }

    /**
     * @param File $file
     * @param Version $version
     * @param VersionProvider $version
     * @param Linker $linker
     */
    public function publish(File $file, Version $version, VersionProvider $versionProvider, Linker $linker)
    {
        /** @var Model $result */
        $this->client->putObject(
            array(
                'Bucket' => $this->bucket,
                'Key'    => $linker->getLink($file, $version, $versionProvider->getExtension($file, $version)),
                'SourceFile' => $this->storage->retrieveVersion(
                    $versionProvider->getApplicableVersionable($file),
                    $version
                ),
                'ACL' => CannedAcl::PUBLIC_READ,
                'ContentType' => $versionProvider->getMimeType($file, $version),
            )
        );
    }

    /**
     * @param File $file
     * @param Version $version
     * @param VersionProvider $versionProvider
     * @param Linker $linker
     */
    public function unpublish(File $file, Version $version, VersionProvider $versionProvider, Linker $linker)
    {
        $this->client->deleteObject(
            array(
                'Bucket' => $this->bucket,
                'Key' => $linker->getLink($file, $version, $versionProvider->getExtension($file, $version))
            )
        );
    }

    /**
     * @param File $file
     * @param Version $version
     * @param VersionProvider $versionProvider
     * @param Linker $linker
     * @return string
     */
    public function getUrl(File $file, Version $version, VersionProvider $versionProvider, Linker $linker)
    {
        return $this->client->getObjectUrl(
            $this->bucket,
            $linker->getLink($file, $version, $versionProvider->getExtension($file, $version))
        );
    }
}
