<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\AbstractOperator;
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
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;

/**
 * File operator
 *
 * @author pekkis
 *
 */
class FileOperator extends AbstractOperator
{
    const COMMAND_UPLOAD = 'upload';
    const COMMAND_AFTERUPLOAD = 'after_upload';
    const COMMAND_UPDATE = 'update';
    const COMMAND_DELETE = 'delete';
    const COMMAND_COPY = 'copy';

    /**
     * Default command strategies
     *
     * @var array
     */
    protected $commandStrategies = array(
        self::COMMAND_UPLOAD => ExecutionStrategy::STRATEGY_SYNCHRONOUS,
        self::COMMAND_AFTERUPLOAD => ExecutionStrategy::STRATEGY_SYNCHRONOUS,
        self::COMMAND_UPDATE => ExecutionStrategy::STRATEGY_SYNCHRONOUS,
        self::COMMAND_DELETE => ExecutionStrategy::STRATEGY_SYNCHRONOUS,
        self::COMMAND_COPY => ExecutionStrategy::STRATEGY_SYNCHRONOUS,
    );

    /**
     * @var array Profiles
     */
    private $profiles = array();

    /**
     * Adds a file profile
     *
     * @param  FileProfile              $profile
     * @return FileLibrary
     * @throws InvalidArgumentException
     */
    public function addProfile(FileProfile $profile)
    {
        $profile->setFileOperator($this);

        $identifier = $profile->getIdentifier();
        if (isset($this->profiles[$identifier])) {
            throw new InvalidArgumentException("Profile '{$identifier}' already exists");
        }
        $this->profiles[$identifier] = $profile;

        $this->getEventDispatcher()->addSubscriber($profile);

        $event = new FileProfileEvent($profile);
        $this->getEventDispatcher()->dispatch(Events::PROFILE_AFTER_ADD, $event);

        return $this;
    }

    /**
     * Returns a file profile
     *
     * @param  string                   $identifier File profile identifier
     * @throws InvalidArgumentException
     * @return FileProfile
     */
    public function getProfile($identifier)
    {
        if (!isset($this->profiles[$identifier])) {
            throw new InvalidArgumentException("File profile '{$identifier}' not found");
        }

        return $this->profiles[$identifier];
    }

    /**
     * Returns all file profiles
     *
     * @return FileProfile[] Array of file profiles
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    /**
     * Updates a file
     *
     * @param  File         $file
     * @return FileOperator
     */
    public function update(File $file)
    {
        return $this->executeOrQueue(
            $this->createCommand('Xi\Filelib\File\Command\UpdateFileCommand', array($file)),
            self::COMMAND_UPDATE
        );
    }

    /**
     * Finds a file
     *
     * @param  mixed      $id File id
     * @return File
     */
    public function find($id)
    {
        $file = $this->getBackend()->findById($id, 'Xi\Filelib\File\File');

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
        $file = $this->getBackend()->findByFinder(
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
        $files = $this->getBackend()->findByFinder(new FileFinder());

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
            $folder = $this->getFolderOperator()->findRoot();
        }

        return $this->executeOrQueue(
            $this->createCommand('Xi\Filelib\File\Command\UploadFileCommand', array($upload, $folder, $profile)),
            self::COMMAND_UPLOAD
        );
    }

    /**
     * Deletes a file
     *
     * @param File $file
     */
    public function delete(File $file)
    {
        return $this->executeOrQueue(
            $this->createCommand('Xi\Filelib\File\Command\DeleteFileCommand', array($file)),
            self::COMMAND_DELETE
        );
    }

    /**
     * Copies a file to folder
     *
     * @param File   $file
     * @param Folder $folder
     */
    public function copy(File $file, Folder $folder)
    {
        return $this->executeOrQueue(
            $this->createCommand('Xi\Filelib\File\Command\CopyFileCommand', array($file, $folder)),
            self::COMMAND_COPY
        );

    }

    /**
     * Returns whether a file has a certain version
     *
     * @param  File    $file    File item
     * @param  string  $version Version
     * @return boolean
     */
    public function hasVersion(File $file, $version)
    {
        $profile = $this->getProfile($file->getProfile());
        return $profile->fileHasVersion($file, $version);
    }

    /**
     * Returns version provider for a file/version
     *
     * @param  File            $file    File item
     * @param  string          $version Version
     * @return VersionProvider Provider
     */
    public function getVersionProvider(File $file, $version)
    {
        $profile = $this->getProfile($file->getProfile());

        return $profile->getVersionProvider($file, $version);
    }

    /**
     *
     * @return FolderOperator
     */
    public function getFolderOperator()
    {
        return $this->getFilelib()->getFolderOperator();
    }
}
