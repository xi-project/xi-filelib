<?php

namespace Xi\Filelib\Migration;

use Xi\Filelib\Command;
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

    public function __construct(FileLibrary $filelib)
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

            $profile = $this->filelib->getFileOperator()->getProfile($file->getProfile());

            $file->setUuid($this->filelib->getFileOperator()->generateUuid());
            $this->filelib->getFileOperator()->update($file);

            $resource = $file->getResource();
            $retrieved = $this->filelib->getStorage()->retrieve($resource);
            $resource->setHash(sha1_file($retrieved));
            $resource->setVersions($profile->getFileVersions($file));
            $this->filelib->getBackend()->updateResource($resource);

        }

        $folder = $this->filelib->getFolderOperator()->findRoot();
        $this->createUuidToFolder($folder);

    }


    /**
     * @see Enqueueable::getEnqueueReturnValue
     */
    public function getEnqueueReturnValue()
    {
        return true;
    }

    /**
     * @see Serializable::serialize
     */
    public function serialize()
    {
        return '';
    }

    /**
     * @see Serializable::unserialize
     */
    public function unserialize($serialized)
    {
        return;
    }

    /**
     * @param Folder $folder
     */
    private function createUuidToFolder(Folder $folder)
    {
        $folder->setUuid($this->filelib->getFolderOperator()->generateUuid());
        $this->filelib->getFolderOperator()->update($folder);

        foreach ($this->filelib->getFolderOperator()->findSubFolders($folder) as $subfolder) {
            $this->createUuidToFolder($subfolder);
        }
    }


}
