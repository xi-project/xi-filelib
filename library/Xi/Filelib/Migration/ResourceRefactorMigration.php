<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Migration;

use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\Command\Command;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\Folder;

/**
 * Migration command to be run after resourcification (v0.7.0)
 */
class ResourceRefactorMigration implements Command
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    public function attachTo(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }

    /**
     * @see Command::execute
     */
    public function execute()
    {
        $files = $this->filelib->getFileRepository()->findAll();

        foreach ($files as $file) {

            $profile = $this->filelib->getFileRepository()->getProfile($file->getProfile());

            $file->setUuid(Uuid::uuid4()->toString());
            $this->filelib->getFileRepository()->update($file);

            $resource = $file->getResource();

            try {
                $retrieved = $this->filelib->getStorage()->retrieve($resource);
                $resource->setHash(sha1_file($retrieved));
                $resource->setVersions($profile->getFileVersions($file));
                $this->filelib->getBackend()->updateResource($resource);
            } catch (\Exception $e) {
                // Loo
            }

        }

        $folder = $this->filelib->getFolderRepository()->findRoot();
        $this->createUuidToFolder($folder);

    }

    /**
     * @param Folder $folder
     */
    private function createUuidToFolder(Folder $folder)
    {
        $folder->setUuid(Uuid::uuid4()->toString());
        $this->filelib->getFolderRepository()->update($folder);

        foreach ($this->filelib->getFolderRepository()->findSubFolders($folder) as $subfolder) {
            $this->createUuidToFolder($subfolder);
        }
    }

    public function getTopic()
    {
        return 'xi_filelib.command.migration.resource_refactor';
    }

    public function serialize()
    {
        return serialize(array());
    }

    public function unserialize($data)
    {

    }
}
