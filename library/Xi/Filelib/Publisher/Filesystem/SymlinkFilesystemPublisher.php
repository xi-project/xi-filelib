<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Filesystem;

use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\FileLibrary;

/**
 * Publishes files in a filesystem by creating a symlink to the original file in the filesystem storage
 */
class SymlinkFilesystemPublisher extends AbstractFilesystemPublisher implements Publisher
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
        $root,
        $filePermission = 0600,
        $directoryPermission = 0700,
        $baseUrl = '',
        $relativePathToRoot = null
    ) {
        parent::__construct($root, $filePermission, $directoryPermission, $baseUrl);
        $this->relativePathToRoot = $relativePathToRoot;
    }

    public function setDependencies(FileLibrary $filelib)
    {
        $this->storage = $filelib->getStorage();
        parent::setDependencies($filelib);
    }


    /**
     * Sets path from public to private root
     *
     * @param  string                     $relativePathToRoot
     * @return SymlinkFilesystemPublisher
     */
    public function setRelativePathToRoot($relativePathToRoot)
    {
        $this->relativePathToRoot = $relativePathToRoot;

        return $this;
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
     * @param $version
     * @param  int              $levelsDown
     * @return string
     * @throws FilelibException
     */
    public function getRelativePathToVersion(File $file, VersionProvider $versionProvider, $version, $levelsDown = 0)
    {
        $relativePath = $this->getRelativePathToRoot();

        if (!$relativePath) {
            throw new FilelibException('Relative path must be set!');
        }

        $relativePath = str_repeat("../", $levelsDown) . $relativePath;

        $retrieved = $this->storage->retrieveVersion(
            $file->getResource(),
            $version,
            $versionProvider->areSharedVersionsAllowed() ? null : $file
        );

        $path = preg_replace("[^{$this->storage->getRoot()}]", $relativePath, $retrieved);

        return $path;
    }

    /**
     * @param File $file
     * @todo Extract methods. Puuppa code smells bad.
     */
    public function publish(File $file)
    {
        $linker = $this->getLinkerForFile($file);

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
    public function publishVersion(File $file, $version, VersionProvider $versionProvider)
    {
        $linker = $this->getLinkerForFile($file);

        $link = $this->getPublicRoot() . '/' .
            $linker->getLinkVersion($file, $version, $versionProvider->getExtensionFor($version));

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

                $fp = $this->getRelativePathToVersion($file, $versionProvider, $version, $depth);

                // Relative linking requires some movin'n groovin.
                $oldCwd = getcwd();
                chdir($path);
                symlink($fp, $link);
                chdir($oldCwd);
            } else {
                symlink(
                    $this->storage->retrieveVersion(
                        $file->getResource(),
                        $version,
                        $versionProvider->areSharedVersionsAllowed() ? null: $file
                    ),
                    $link
                );
            }

        }

    }

    public function unpublish(File $file)
    {
        $linker = $this->getLinkerForFile($file);
        $link = $this->getPublicRoot() . '/' . $linker->getLink($file);

        if (is_link($link)) {
            unlink($link);
        }
    }

    public function unpublishVersion(File $file, $version, VersionProvider $versionProvider)
    {
        $linker = $this->getLinkerForFile($file);
        $link = $this->getPublicRoot() . '/' .
            $linker->getLinkVersion($file, $version, $versionProvider->getExtensionFor($version));

        if (is_link($link)) {
            unlink($link);
        }
    }
}
