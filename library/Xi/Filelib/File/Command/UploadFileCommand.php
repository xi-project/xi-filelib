<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File\Command;

use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\Event\FolderEvent;
use Xi\Filelib\Events;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\Queue\UuidReceiver;
use Xi\Filelib\Resource\ResourceRepository;

class UploadFileCommand extends BaseFileCommand implements UuidReceiver
{
    /**
     * @var ProfileManager
     */
    private $profiles;

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
    protected $uuid = null;

    /**
     * @var ResourceRepository
     */
    protected $resourceRepository;

    public function __construct(FileUpload $upload, Folder $folder, $profile = 'default')
    {
        $this->upload = $upload;
        $this->folder = $folder;
        $this->profile = $profile;
    }

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->profiles = $filelib->getProfileManager();
        $this->resourceRepository = $filelib->getResourceRepository();
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid ?: Uuid::uuid4()->toString();
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    public function execute()
    {
        $upload = $this->upload;
        $folder = $this->folder;
        $profile = $this->profile;

        $event = new FolderEvent($folder);
        $this->eventDispatcher->dispatch(Events::FOLDER_BEFORE_WRITE_TO, $event);

        $profileObj = $this->profiles->getProfile($profile);
        $event = new FileUploadEvent($upload, $folder, $profileObj);
        $this->eventDispatcher->dispatch(Events::FILE_UPLOAD, $event);

        $upload = $event->getFileUpload();

        $file = File::create(
            array(
                'folder_id' => $folder->getId(),
                'name' => $upload->getUploadFilename(),
                'profile' => $profile,
                'date_created' => $upload->getDateUploaded(),
                'uuid' => $this->getUuid(),
            )
        );

        $file->setStatus(File::STATUS_RAW);

        $resource = $this->resourceRepository->findResourceForUpload($file, $upload);
        $file->setResource($resource);

        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_CREATE, $event);

        $this->backend->createFile($file, $folder);

        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_CREATE, $event);

        $this->fileRepository->createExecutable(
            FileRepository::COMMAND_AFTERUPLOAD,
            array($file)
        )->execute();

        return $file;
    }

    public function getTopic()
    {
        return 'xi_filelib.command.file.upload';
    }
}
