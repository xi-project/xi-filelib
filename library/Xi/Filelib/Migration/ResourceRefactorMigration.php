<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Migration;

use Xi\Filelib\AbstractCommand;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\Folder;

/**
 * Migration command to be run after resourcification (v0.7.0)
 */
class ResourceRefactorMigration extends AbstractCommand
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
        $files = $this->filelib->getFileOperator()->findAll();

        foreach ($files as $file) {

            $this->getOutput()->writeln("Processing file #{$file->getId()}");

            $profile = $this->filelib->getFileOperator()->getProfile($file->getProfile());

            $file->setUuid($this->filelib->getFileOperator()->generateUuid());
            $this->filelib->getFileOperator()->update($file);

            $resource = $file->getResource();



            try {
                $this->getOutput()->writeln("Setting hash for resource #{$resource->getId()}");
                $retrieved = $this->filelib->getStorage()->retrieve($resource);
                $resource->setHash(sha1_file($retrieved));
                $resource->setVersions($profile->getFileVersions($file));
                $this->filelib->getBackend()->updateResource($resource);
            } catch (\Exception $e) {
                $this->getOutput()->writeln("Failed to set hash for resource #{$resource->getId()}");
            }

        }

        $folder = $this->filelib->getFolderOperator()->findRoot();
        $this->createUuidToFolder($folder);

    }

    /**
     * @param Folder $folder
     */
    private function createUuidToFolder(Folder $folder)
    {
        $this->getOutput()->writeln("Processing folder #{$folder->getId()}");

        $folder->setUuid($this->filelib->getFolderOperator()->generateUuid());
        $this->filelib->getFolderOperator()->update($folder);

        foreach ($this->filelib->getFolderOperator()->findSubFolders($folder) as $subfolder) {
            $this->createUuidToFolder($subfolder);
        }
    }
}
