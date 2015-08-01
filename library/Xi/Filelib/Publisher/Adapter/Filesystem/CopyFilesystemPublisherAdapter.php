<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Adapter\Filesystem;

use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Publisher\Linker;
use Xi\Filelib\Publisher\PublisherAdapter;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Versionable\Version;

/**
 * Publishes files in a filesystem by retrieving them from storage and creating a copy
 *
 * @author pekkis
 *
 */
class CopyFilesystemPublisherAdapter extends BaseFilesystemPublisherAdapter implements PublisherAdapter
{
    /**
     * @var Storage
     */
    private $storage;

    public function attachTo(FileLibrary $filelib)
    {
        $this->storage = $filelib->getStorage();
    }

    public function publish(File $file, Version $version, VersionProvider $versionProvider, Linker $linker)
    {
        $link = $this->getPublicRoot() . '/' .
            $linker->getLink(
                $file,
                $version,
                $versionProvider->getExtension($file, $version)
            );

        if (!is_file($link)) {

            $path = dirname($link);

            if (!is_dir($path)) {
                mkdir($path, $this->getDirectoryPermission(), true);
            }

            $tmp = $this->storage->retrieve(
                $file->getVersion($version)->getResource()
            );

            copy($tmp, $link);
            chmod($link, $this->getFilePermission());
        }
    }

    public function unpublish(File $file, Version $version, VersionProvider $versionProvider, Linker $linker)
    {
        $link = $this->getPublicRoot() . '/' .
            $linker->getLink(
                $file,
                $version,
                $versionProvider->getExtension($file, $version)
            );
        if (is_file($link)) {
            unlink($link);
        }
    }
}
