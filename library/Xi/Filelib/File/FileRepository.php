<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File;

use Xi\Filelib\Command\CommandDefinition;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\AbstractRepository;
use Xi\Filelib\FilelibException;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Event\FileProfileEvent;
use Xi\Filelib\Backend\Finder\FileFinder;
use ArrayIterator;
use Xi\Filelib\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Profile\FileProfile;

/**
 * File operator
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
     * Finds a file
     *
     * @param  mixed      $id File id
     * @return File
     */
    public function find($id)
    {
        $file = $this->backend->findById($id, 'Xi\Filelib\File\File');

        if (!$file) {
            return false;
        }

        return $file;
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
        $file = $this->backend->findByFinder(
            new FileFinder(array('folder_id' => $folder->getId(), 'name' => $filename))
        )->current();

        if (!$file) {
            return false;
        }

        return $file;
    }

    /**
     * Finds and returns all files
     *
     * @return ArrayIterator
     */
    public function findAll()
    {
        $files = $this->backend->findByFinder(new FileFinder());

        return $files;
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
