<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Migration;

use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Command\Command;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\File\File;

/**
 * Migration command to be run after resourcification (v0.7.0)
 */
class ResourceRefactorMigration implements Command
{
    /**
     * @var ProfileManager
     */
    private $profiles;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var FileRepository
     */
    private $fileRepository;

    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * @var Backend
     */
    private $backend;

    public function attachTo(FileLibrary $filelib)
    {
        $this->filelib = $filelib;

        $this->profiles = $filelib->getProfileManager();
        $this->fileRepository = $filelib->getFileRepository();
        $this->folderRepository = $filelib->getFolderRepository();
        $this->storage = $filelib->getStorage();
        $this->backend = $filelib->getBackend();
    }

    /**
     * @see Command::execute
     */
    public function execute()
    {
        $files = $this->fileRepository->findAll();

        foreach ($files as $file) {
            /** @var File $file */

            $profile = $this->profiles->getProfile($file->getProfile());

            $file->setUuid(Uuid::uuid4()->toString());
            $this->fileRepository->update($file);

            $resource = $file->getResource();

            try {
                $retrieved = $this->storage->retrieve($resource);
                $resource->setHash(sha1_file($retrieved));
                $resource->setVersions($profile->getFileVersions($file));
                $this->filelib->getBackend()->updateResource($resource);
            } catch (\Exception $e) {
                // Loo
            }

        }

        $folder = $this->folderRepository->findRoot();
        $this->createUuidToFolder($folder);

    }

    /**
     * @param Folder $folder
     */
    private function createUuidToFolder(Folder $folder)
    {
        $folder->setUuid(Uuid::uuid4()->toString());
        $this->folderRepository->update($folder);

        foreach ($this->folderRepository->findSubFolders($folder) as $subfolder) {
            $this->createUuidToFolder($subfolder);
        }
    }

    public function getTopic()
    {
        return 'xi_filelib.command.migration.resource_refactor';
    }
}
