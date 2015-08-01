<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File;

use Rhumsaa\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PhpCollection\Sequence;
use Xi\Filelib\AbstractRepository;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Event\FileCopyEvent;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\Event\FolderEvent;
use Xi\Filelib\Events;
use Xi\Filelib\File\FileCopier;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\FilelibException;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\Resource\ResourceRepository;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Storage\Storage;

/**
 * File repository
 *
 * @author pekkis
 *
 */
class FileRepository extends AbstractRepository implements FileRepositoryInterface
{
    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ResourceRepository
     */
    private $resourceRepository;

    /**
     * @var ProfileManager
     */
    private $profiles;

    /**
     * @var Storage
     */
    private $storage;

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->resourceRepository = $filelib->getResourceRepository();
        $this->folderRepository = $filelib->getFolderRepository();
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->profiles = $filelib->getProfileManager();
        $this->storage = $filelib->getStorage();
    }

    /**
     * Updates a file
     *
     * @param  File         $file
     * @return FileRepository
     */
    public function update(File $file)
    {
        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_UPDATE, $event);
        $this->resourceRepository->update($file->getResource());
        $this->backend->updateFile($file);

        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_UPDATE, $event);

        return $file;
    }

    /**
     * Finds file by id
     *
     * @param  mixed $id File id or array of file ids
     * @return File
     */
    public function find($id)
    {
        return $this->findMany(array($id))->first();
    }

    /**
     * @return Sequence
     */
    public function findMany($ids)
    {
        return $this->backend->findByIds($ids, 'Xi\Filelib\File\File');
    }

    /**
     * @param FileFinder $finder
     * @return Sequence
     */
    public function findBy(FileFinder $finder)
    {
        return $this->backend->findByFinder($finder);
    }


    /**
     * @param $uuid
     * @return File
     */
    public function findByUuid($uuid)
    {
        return $this->findBy(new FileFinder(array('uuid' => $uuid)))->first();
    }


    /**
     * Finds file by filename in a folder
     *
     * @param Folder $folder
     * @param $filename
     * @return File
     */
    public function findByFilename(Folder $folder, $filename)
    {
        return $this->backend->findByFinder(
            new FileFinder(array('folder_id' => $folder->getId(), 'name' => $filename))
        )->first();
    }

    /**
     * Finds and returns all files
     *
     * @return Sequence
     */
    public function findAll()
    {
        return $this->backend->findByFinder(new FileFinder());
    }

    /**
     * Uploads a file
     *
     * @param  mixed            $upload Uploadable, path or object
     * @param  Folder           $folder
     * @return File
     * @throws FilelibException
     */
    public function upload($upload, Folder $folder = null, $profile = 'default')
    {
        if (!$upload instanceof FileUpload) {
            $upload = new FileUpload($upload);
        }

        if (!$folder) {
            $folder = $this->folderRepository->findRoot();
        }

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
                'uuid' => Uuid::uuid4()->toString(),
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

        return $file;
    }

    public function afterUpload(File $file)
    {
        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_AFTERUPLOAD, $event);

        $file->setStatus(File::STATUS_COMPLETED);

        $this->update($file);

        return $file;
    }


    /**
     * Deletes a file
     *
     * @param File $file
     */
    public function delete(File $file)
    {
        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_DELETE, $event);

        $this->backend->deleteFile($file);

        $file->setStatus(File::STATUS_DELETED);

        if ($file->getResource()->isExclusive()) {
            $this->resourceRepository->delete($file->getResource());
        }

        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_DELETE, $event);

        return true;
    }

    /**
     * Copies a file to folder
     *
     * @param File   $file
     * @param Folder $folder
     *
     * @return File
     */
    public function copy(File $file, Folder $folder)
    {
        $event = new FolderEvent($folder);
        $this->eventDispatcher->dispatch(Events::FOLDER_BEFORE_WRITE_TO, $event);

        $copier = new FileCopier(
            $this->resourceRepository,
            $this,
            $this->storage
        );

        $copy = $copier->copy($file, $folder);

        $event = new FileCopyEvent($file, $copy);
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_COPY, $event);

        $this->backend->createFile($copy, $folder);

        $event = new FileCopyEvent($file, $copy);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_COPY, $event);

        return $copy;
    }
}
