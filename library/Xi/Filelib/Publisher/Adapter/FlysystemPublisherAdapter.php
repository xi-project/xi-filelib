<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Adapter;

use League\Flysystem\AdapterInterface;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Publisher\PublisherAdapter;
use Xi\Filelib\Attacher;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Publisher\Linker;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Versionable\Version;
use League\Flysystem\Filesystem;

class FlysystemPublisherAdapter implements PublisherAdapter
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param Filesystem $filesystem
     * @param string $endpoint
     */
    public function __construct(Filesystem $filesystem, $endpoint)
    {
        $this->filesystem = $filesystem;
        $this->endpoint = rtrim($endpoint, '/') . '/';
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
     * @param VersionProvider $versionProvider
     * @param Linker $linker
     * @return bool
     */
    public function publish(File $file, Version $version, VersionProvider $versionProvider, Linker $linker)
    {
        $path = $linker->getLink(
            $file,
            $version,
            $versionProvider->getExtension($file, $version)
        );

        $tmp = $this->storage->retrieve(
            $file->getVersion($version)->getResource()
        );

        if ($this->filesystem->has($path)) {
            return false;
        }

        $this->filesystem->write(
            $path,
            file_get_contents($tmp),
            [
                'visibility' => AdapterInterface::VISIBILITY_PUBLIC
            ]
        );
        return true;
    }

    /**
     * @param File $file
     * @param Version $version
     * @param VersionProvider $versionProvider
     * @param Linker $linker
     * @return bool
     */
    public function unpublish(File $file, Version $version, VersionProvider $versionProvider, Linker $linker)
    {
        $path = $linker->getLink(
            $file,
            $version,
            $versionProvider->getExtension($file, $version)
        );

        if (!$this->filesystem->has($path)) {
            return false;
        }

        $this->filesystem->delete($path);
        return true;
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
        $url = $this->endpoint;
        $url .= $linker->getLink(
            $file,
            $version,
            $versionProvider->getExtension($file, $version)
        );

        return $url;
    }
}
