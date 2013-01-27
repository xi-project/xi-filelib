<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Filesystem;

use Xi\Filelib\File\File;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\File\FileOperator;

/**
 * Publishes files in a filesystem by retrieving them from storage and creating a copy
 *
 * @author pekkis
 *
 */
class CopyFilesystemPublisher extends AbstractFilesystemPublisher implements Publisher
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param Storage      $storage
     * @param FileOperator $fileOperator
     * @param array        $options
     */
    public function __construct(Storage $storage, FileOperator $fileOperator, $options = array())
    {
        parent::__construct($fileOperator, $options);
        $this->storage = $storage;
    }

    public function publish(File $file)
    {
        $linker = $this->getLinkerForFile($file);

        $link = $this->getPublicRoot() . '/' . $linker->getLink($file, true);

        if (!is_file($link)) {

            $path = dirname($link);
            if (!is_dir($path)) {
                mkdir($path, $this->getDirectoryPermission(), true);
            }

            $tmp = $this->storage->retrieve($file->getResource());

            copy($tmp, $link);
            chmod($link, $this->getFilePermission());

        }
    }

    public function publishVersion(File $file, $version, VersionProvider $versionProvider)
    {
        $linker = $this->getLinkerForFile($file);

        $link = $this->getPublicRoot() . '/' .
            $linker->getLinkVersion($file, $version, $versionProvider->getExtensionFor($version));

        if (!is_file($link)) {

            $path = dirname($link);

            if (!is_dir($path)) {
                mkdir($path, $this->getDirectoryPermission(), true);
            }

            if ($versionProvider->areSharedVersionsAllowed()) {
                $tmp = $this->storage->retrieveVersion($file->getResource(), $version, null);
            } else {
                $tmp = $this->storage->retrieveVersion($file->getResource(), $version, $file);
            }

            copy($tmp, $link);
            chmod($link, $this->getFilePermission());
        }
    }

    public function unpublish(File $file)
    {
        $linker = $this->getLinkerForFile($file);
        $link = $this->getPublicRoot() . '/' . $linker->getLink($file);

        if (is_file($link)) {
            unlink($link);
        }
    }

    public function unpublishVersion(File $file, $version, VersionProvider $versionProvider)
    {
        $linker = $this->getLinkerForFile($file);
        $link = $this->getPublicRoot() . '/' .
            $linker->getLinkVersion($file, $version, $versionProvider->getExtensionFor($version));

        if (is_file($link)) {
            unlink($link);
        }
    }
}
