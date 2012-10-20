<?php

namespace Xi\Filelib\File\Command;

use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\FilelibException;
use Serializable;
use DateTime;

class UploadFileCommand extends AbstractFileCommand
{
    /**
     *
     * @var FileUpload
     */
    private $upload;

    /**
     *
     * @var Folder
     */
    private $folder;

    /**
     *
     * @var string
     */
    private $profile;

    public function __construct(FileOperator $fileOperator, $upload, Folder $folder, $profile = 'default')
    {
        parent::__construct($fileOperator);

        if (!$upload instanceof FileUpload) {
            $upload = $fileOperator->prepareUpload($upload);
        }

        $this->upload = $upload;
        $this->folder = $folder;
        $this->profile = $profile;
    }

    /**
     * @param File $file
     * @param FileUpload $upload
     * @return Resource
     * @todo This method (isSharedResource() particularly) has the smell of code.
     */
    public function getResource(File $file, FileUpload $upload)
    {
        $file = clone $file;

        $hash = sha1_file($upload->getRealPath());
        $profileObj = $this->fileOperator->getProfile($this->profile);

        $resources = $this->fileOperator->getBackend()->findResourcesByHash($hash);
        if ($resources) {

            foreach ($resources as $resource) {
                if (!$resource->isExclusive()) {
                    $file->setResource($resource);
                    if (!$profileObj->isSharedResourceAllowed($file)) {
                        $file->unsetResource();
                    }
                    break;
                }
            }
        }

        if (!$file->getResource()) {

            $resource = new Resource();
            $resource->setDateCreated(new DateTime());
            $resource->setHash($hash);
            $resource->setSize($upload->getSize());
            $resource->setMimetype($upload->getMimeType());

            $this->fileOperator->getBackend()->createResource($resource);
            $file->setResource($resource);


            if (!$profileObj->isSharedResourceAllowed($file)) {
                $resource->setExclusive(true);
            }

        }

        return $file->getResource();
    }

    public function execute()
    {
        $upload = $this->upload;
        $folder = $this->folder;
        $profile = $this->profile;

        if (!$this->fileOperator->getAcl()->isFolderWritable($folder)) {
            throw new FilelibException("Folder '{$folder->getId()}'not writable");
        }





        $profileObj = $this->fileOperator->getProfile($profile);
        $event = new FileUploadEvent($upload, $folder, $profileObj);

        $this->fileOperator->getEventDispatcher()->dispatch('file.beforeUpload', $event);

        $upload = $event->getFileUpload();

        $file = $this->fileOperator->getInstance(array(
            'folder_id' => $folder->getId(),
            'name' => $upload->getUploadFilename(),
            'profile' => $profile,
            'date_created' => $upload->getDateUploaded(),
            'uuid' => $this->getUuid(),
        ));



        // @todo: actual statuses
        $file->setStatus(File::STATUS_RAW);



        $resource = $this->getResource($file, $upload);

        $file->setResource($resource);
        $this->fileOperator->getBackend()->upload($file, $folder);
        $this->fileOperator->getStorage()->store($resource, $upload->getRealPath());

        $event = new FileEvent($file);
        $this->fileOperator->getEventDispatcher()->dispatch('file.upload', $event);

        $command = $this->fileOperator->createCommand('Xi\Filelib\File\Command\AfterUploadFileCommand', array($this->fileOperator, $file));

        $this->fileOperator->executeOrQueue($command, FileOperator::COMMAND_AFTERUPLOAD);
        return $file;
    }


    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->folder = $data['folder'];
        $this->profile = $data['profile'];
        $this->uuid = $data['uuid'];

        $upload = new FileUpload($data['upload']['realPath']);
        $upload->setOverrideBasename($data['upload']['overrideBasename']);
        $upload->setOverrideFilename($data['upload']['overrideFilename']);
        $upload->setTemporary($data['upload']['temporary']);

        $this->upload = $upload;

    }


    public function serialize()
    {
        $upload = $this->upload;

        $uploadArr = array(
            'overrideBasename' => $upload->getOverrideBasename(),
            'overrideFilename' => $upload->getOverrideFilename(),
            'temporary' => $upload->isTemporary(),
            // 'dateUploaded' => $upload->getDateUploaded(),
            'realPath' => $upload->getRealPath(),
        );


        return serialize(array(
           'folder' => $this->folder,
           'profile' => $this->profile,
           'upload' => $uploadArr,
           'uuid' => $this->uuid,
        ));

    }



}
