<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File;

use Xi\Collections\Collection\ArrayCollection;
use Xi\Filelib\Command\CommandDefinition;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\AbstractRepository;
use Xi\Filelib\FilelibException;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Backend\Finder\FileFinder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * File repository
 *
 * @author pekkis
 *
 */
class FileRepository extends AbstractRepository
{
    const COMMAND_UPLOAD = 'Xi\Filelib\File\Command\UploadFileCommand';
    const COMMAND_AFTERUPLOAD = 'Xi\Filelib\File\Command\AfterUploadFileCommand';
    const COMMAND_UPDATE = 'Xi\Filelib\File\Command\UpdateFileCommand';
    const COMMAND_DELETE = 'Xi\Filelib\File\Command\DeleteFileCommand';
    const COMMAND_COPY = 'Xi\Filelib\File\Command\CopyFileCommand';

    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->folderRepository = $filelib->getFolderRepository();
        $this->eventDispatcher = $filelib->getEventDispatcher();
    }

    /**
     * @return array
     */
    public function getCommandDefinitions()
    {
        return array(
            new CommandDefinition(
                self::COMMAND_UPLOAD
            ),
            new CommandDefinition(
                self::COMMAND_AFTERUPLOAD,
                ExecutionStrategy::STRATEGY_SYNCHRONOUS,
                array(
                    ExecutionStrategy::STRATEGY_SYNCHRONOUS,
                    ExecutionStrategy::STRATEGY_ASYNCHRONOUS,
                )
            ),
            new CommandDefinition(
                self::COMMAND_UPDATE
            ),
            new CommandDefinition(
                self::COMMAND_DELETE
            ),
            new CommandDefinition(
                self::COMMAND_COPY
            ),
        );
    }

    /**
     * Updates a file
     *
     * @param  File         $file
     * @return FileRepository
     */
    public function update(File $file)
    {
        return $this->commander
            ->createExecutable(self::COMMAND_UPDATE, array($file))
            ->execute();
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
     * @return ArrayCollection
     */
    public function findMany($ids)
    {
        return $this->backend->findByIds($ids, 'Xi\Filelib\File\File');
    }

    /**
     * @param FileFinder $finder
     * @return ArrayCollection
     */
    public function findBy(FileFinder $finder)
    {
        return $this->backend->findByFinder($finder);
    }


    /**
     * @param $uuid
     * @return File
     * @todo switch uuid and id as internal primary id
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
     * @return ArrayCollection
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

        return $this->commander
            ->createExecutable(self::COMMAND_UPLOAD, array($upload, $folder, $profile))
            ->execute();
    }

    /**
     * Deletes a file
     *
     * @param File $file
     */
    public function delete(File $file)
    {
        return $this->commander
            ->createExecutable(self::COMMAND_DELETE, array($file))
            ->execute();
    }

    /**
     * Copies a file to folder
     *
     * @param File   $file
     * @param Folder $folder
     */
    public function copy(File $file, Folder $folder)
    {
        return $this->commander
            ->createExecutable(self::COMMAND_COPY, array($file, $folder))
            ->execute();
    }
}
