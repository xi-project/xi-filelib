<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Adapter\Filesystem;

use Xi\Filelib\Publisher\PublisherAdapter;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Publisher\Linker;

/**
 * Publishes files in a filesystem by creating a symlink to the original file in the filesystem storage
 */
class SymlinkFilesystemPublisherAdapter extends AbstractFilesystemPublisherAdapter implements PublisherAdapter
{
    /**
     * @var FilesystemStorage
     */
    private $storage;

    /**
     * @var string Relative path from publisher root to storage root
     */
    private $relativePathToRoot;

    public function __construct(
        $publicRoot,
        $filePermission = "600",
        $directoryPermission = "700",
        $baseUrl = '',
        $relativePathToRoot = null
    ) {
        parent::__construct($publicRoot, $filePermission, $directoryPermission, $baseUrl);
        $this->relativePathToRoot = $relativePathToRoot;
    }

    public function setDependencies(FileLibrary $filelib)
    {
        $this->storage = $filelib->getStorage();
        if (!$this->storage instanceof FilesystemStorage) {
            throw new \InvalidArgumentException("Invalid storage");
        }
    }

    /**
     * Returns path from public to private root
     *
     * @return string
     */
    public function getRelativePathToRoot()
    {
        return $this->relativePathToRoot;
    }

    /**
     * @param  File             $file
     * @param  int              $levelsDown
     * @return string
     * @throws FilelibException
     */
    public function getRelativePathTo(File $file, $levelsDown = 0)
    {
        $relativePath = $this->getRelativePathToRoot();

        if (!$relativePath) {
            throw new FilelibException('Relative path must be set!');
        }
        $relativePath = str_repeat("../", $levelsDown) . $relativePath;

        $retrieved = $this->storage->retrieve($file->getResource());

        $path = preg_replace("[^{$this->storage->getRoot()}]", $relativePath, $retrieved);

        return $path;
    }

    /**
     * @param File            $file
     * @param VersionProvider $versionProvider
     * @param  int              $levelsDown
     * @return string
     * @throws FilelibException
     */
    public function getRelativePathToVersion(File $file, VersionProvider $versionProvider, $levelsDown = 0)
    {
        $relativePath = $this->getRelativePathToRoot();

        if (!$relativePath) {
            throw new FilelibException('Relative path must be set!');
        }

        $relativePath = str_repeat("../", $levelsDown) . $relativePath;

        $retrieved = $this->storage->retrieveVersion(
            $file->getResource(),
            $versionProvider->getIdentifier(),
            $versionProvider->areSharedVersionsAllowed() ? null : $file
        );

        $path = preg_replace("[^{$this->storage->getRoot()}]", $relativePath, $retrieved);

        return $path;
    }

    /**
     * @param File $file
     * @todo Extract methods. Puuppa code smells bad.
     */
    public function publish(File $file, Linker $linker)
    {
        $link = $this->getPublicRoot() . '/' . $linker->getLink($file, true);

        if (!is_link($link)) {
            $path = dirname($link);

            if (!is_dir($path)) {
                mkdir($path, $this->getDirectoryPermission(), true);
            }

            if ($this->getRelativePathToRoot()) {

                $path2 = substr($path, strlen($this->getPublicRoot()) + 1);

                // If the link goes to the root dir, $path2 is false and fuxors the depth without a check.
                if ($path2 === false) {
                    $depth = 0;
                } else {
                    $depth = sizeof(explode(DIRECTORY_SEPARATOR, $path2));
                }

                // Relative linking requires some movin'n groovin.
                $oldCwd = getcwd();
                chdir($path);
                symlink($this->getRelativePathTo($file, $depth), $link);
                chdir($oldCwd);
            } else {
                symlink($this->storage->retrieve($file->getResource()), $link);
            }
        }

    }

    /**
     * @param File            $file
     * @param string          $version
     * @param VersionProvider $versionProvider
     * @todo Refactor. Puuppa code smells.
     */
    public function publishVersion(File $file, VersionProvider $version, Linker $linker)
    {
        $link = $this->getPublicRoot() . '/' .
            $linker->getLinkVersion($file, $version->getIdentifier(), $version->getExtensionFor($file, $version));

        if (!is_link($link)) {

            $path = dirname($link);
            if (!is_dir($path)) {
                mkdir($path, $this->getDirectoryPermission(), true);
            }

            if ($this->getRelativePathToRoot()) {

                $path2 = substr($path, strlen($this->getPublicRoot()) + 1);

                // If the link goes to the root dir, $path2 is false and fuxors the depth without a check.
                if ($path2 === false) {
                    $depth = 0;
                } else {
                    $depth = sizeof(explode(DIRECTORY_SEPARATOR, $path2));
                }

                $fp = $this->getRelativePathToVersion($file, $version, $depth);

                // Relative linking requires some movin'n groovin.
                $oldCwd = getcwd();
                chdir($path);
                symlink($fp, $link);
                chdir($oldCwd);
            } else {
                symlink(
                    $this->storage->retrieveVersion(
                        $file->getResource(),
                        $version->getIdentifier(),
                        $version->areSharedVersionsAllowed() ? null: $file
                    ),
                    $link
                );
            }

        }

    }

    public function unpublish(File $file, Linker $linker)
    {
        $link = $this->getPublicRoot() . '/' . $linker->getLink($file);
        if (is_link($link)) {
            unlink($link);
        }
    }

    public function unpublishVersion(File $file, VersionProvider $version, Linker $linker)
    {
        $link = $this->getPublicRoot() . '/' .
            $linker->getLinkVersion($file, $version->getIdentifier(), $version->getExtensionFor($file, $version));
        if (is_link($link)) {
            unlink($link);
        }
    }
}
