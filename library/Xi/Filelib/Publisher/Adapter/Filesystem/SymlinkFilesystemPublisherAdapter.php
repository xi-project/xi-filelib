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
use Xi\Filelib\LogicException;
use Xi\Filelib\InvalidArgumentException;

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

    public function attachTo(FileLibrary $filelib)
    {
        $this->storage = $filelib->getStorage();
        if (!$this->storage instanceof FilesystemStorage) {
            throw new InvalidArgumentException("Symlink filesystem publisher requires filesystem storage");
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
     * @param File            $file
     * @param VersionProvider $versionProvider
     * @param  int              $levelsDown
     * @return string
     * @throws FilelibException
     */
    public function getRelativePathToVersion(File $file, $version, VersionProvider $versionProvider, $levelsDown = 0)
    {
        $relativePath = $this->getRelativePathToRoot();

        if (!$relativePath) {
            throw new LogicException('Relative path must be set!');
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
     * @param File            $file
     * @param string          $version
     * @param VersionProvider $versionProvider
     * @todo Refactor. Puuppa code smells.
     */
    public function publish(File $file, $version, VersionProvider $versionProvider, Linker $linker)
    {
        $link = $this->getPublicRoot() . '/' .
            $linker->getLink(
                $file,
                $version,
                $versionProvider->getExtensionFor($file, $version)
            );

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

                $fp = $this->getRelativePathToVersion($file, $version, $versionProvider, $depth);

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

    public function unpublish(File $file, $version, VersionProvider $versionProvider, Linker $linker)
    {
        $link = $this->getPublicRoot() . '/' .
            $linker->getLink($file, $version, $versionProvider->getExtensionFor($file, $version));
        if (is_link($link)) {
            unlink($link);
        }
    }
}
