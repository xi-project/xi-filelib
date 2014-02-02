<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File\Command;

use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\FolderEvent;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Backend\Finder\ResourceFinder;
use DateTime;
use Xi\Filelib\Events;
use Pekkis\Queue\Message;

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

    /**
     * @var string
     */
    protected $uuid;

    public function __construct(FileUpload $upload, Folder $folder, $profile = 'default', $uuid = null)
    {
        $this->upload = $upload;
        $this->folder = $folder;
        $this->profile = $profile;
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid ?: Uuid::uuid4()->toString();
    }

    /**
     * @param  File       $file
     * @param  FileUpload $upload
     * @return Resource
     * @todo This method (isSharedResource() particularly) has the smell of code.
     */
    public function getResource(File $file, FileUpload $upload)
    {
        $file = clone $file;


        $hash = sha1_file($upload->getRealPath());
        $profileObj = $this->fileOperator->getProfile($this->profile);

        $finder = new ResourceFinder(array('hash' => $hash));
        $resources = $this->fileOperator->getBackend()->findByFinder($finder);

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
            $resource->setVersions(array());

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

        $event = new FolderEvent($folder);
        $this->fileOperator->getEventDispatcher()->dispatch(Events::FOLDER_BEFORE_WRITE_TO, $event);

        $profileObj = $this->fileOperator->getProfile($profile);
        $event = new FileUploadEvent($upload, $folder, $profileObj);
        $this->fileOperator->getEventDispatcher()->dispatch(Events::FILE_BEFORE_CREATE, $event);

        $upload = $event->getFileUpload();

        $file = File::create(
            array(
                'folder_id' => $folder->getId(),
                'name' => $upload->getUploadFilename(),
                'profile' => $profile,
                'date_created' => $upload->getDateUploaded(),
                'uuid' => $this->getUuid(),
                'versions' => array()
            )
        );

        // @todo: actual statuses
        $file->setStatus(File::STATUS_RAW);

        $resource = $this->getResource($file, $upload);

        $file->setResource($resource);
        $this->fileOperator->getBackend()->createFile($file, $folder);
        $this->fileOperator->getStorage()->store($resource, $upload->getRealPath());

        $event = new FileEvent($file);
        $this->fileOperator->getEventDispatcher()->dispatch(Events::FILE_AFTER_CREATE, $event);

        $command = $this->fileOperator->createCommand(
            'Xi\Filelib\File\Command\AfterUploadFileCommand',
            array($file)
        );

        $this->fileOperator->executeOrQueue($command, FileOperator::COMMAND_AFTERUPLOAD);

        return $file;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        $uploadArr = array(
            'overrideBasename' => $this->upload->getOverrideBasename(),
            'overrideFilename' => $this->upload->getOverrideFilename(),
            'temporary' => $this->upload->isTemporary(),
            'realPath' => $this->upload->getRealPath(),
        );

        return Message::create(
            'xi_filelib.command.file.upload',
            array(
                'folder_id' => $this->folder->getId(),
                'upload' => $uploadArr,
                'profile' => $this->profile,
            )
        );
    }


}
