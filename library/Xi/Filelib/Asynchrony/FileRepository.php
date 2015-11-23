<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony;

use Xi\Collections\Collection\ArrayCollection;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepositoryInterface;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\LogicException;

class FileRepository implements FileRepositoryInterface
{
    const COMMAND_UPLOAD = 'xi_filelib.asynchrony.upload';
    const COMMAND_AFTERUPLOAD = 'xi_filelib.asynchrony.afterUpload';
    const COMMAND_DELETE = 'xi_filelib.asynchrony.delete';
    const COMMAND_UPDATE = 'xi_filelib.asynchrony.update';
    const COMMAND_COPY = 'xi_filelib.asynchrony.copy';

    /**
     * @var FileRepositoryInterface
     */
    private $innerRepository;

    /**
     * @var Asynchrony
     */
    private $asynchrony;

    private $strategies = [];

    /**
     * @param FileRepositoryInterface $innerRepository
     */
    public function __construct(
        FileRepositoryInterface $innerRepository,
        Asynchrony $asynchrony
    ) {
        $this->innerRepository = $innerRepository;
        $this->asynchrony = $asynchrony;

        $this->strategies = [
            self::COMMAND_UPLOAD => ExecutionStrategies::STRATEGY_SYNC,
            self::COMMAND_AFTERUPLOAD => ExecutionStrategies::STRATEGY_SYNC,
            self::COMMAND_DELETE => ExecutionStrategies::STRATEGY_SYNC,
            self::COMMAND_UPDATE => ExecutionStrategies::STRATEGY_SYNC,
            self::COMMAND_COPY => ExecutionStrategies::STRATEGY_SYNC,
        ];
    }

    /**
     * @return FileRepositoryInterface
     */
    public function getInnerRepository()
    {
        return $this->innerRepository;
    }

    /**
     * @param $command
     * @throws LogicException
     */
    public function getExecutionStrategy($command)
    {
        if (!isset($this->strategies[$command])) {
            throw new LogicException(
                sprintf(
                    "Command '%s' does not exist",
                    $command
                )
            );
        }

        return $this->strategies[$command];
    }

    /**
     * @param $command
     * @param $strategy
     * @throws LogicException
     */
    public function setExecutionStrategy($command, $strategy)
    {
        if (!isset($this->strategies[$command])) {
            throw new LogicException(
                sprintf(
                    "Command '%s' does not exist",
                    $command
                )
            );
        }

        $this->strategies[$command] = $strategy;
        return $this;
    }

    public function upload($upload, Folder $folder = null, $profile = 'default')
    {
        return $this->execute(
            self::COMMAND_UPLOAD,
            [$this->innerRepository, 'upload'],
            [
                $upload,
                $folder,
                $profile
            ]
        );
    }

    public function afterUpload(File $file)
    {
        return $this->execute(
            self::COMMAND_AFTERUPLOAD,
            [$this->innerRepository, 'afterUpload'],
            [
                $file,
            ]
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
        return $this->execute(
            self::COMMAND_AFTERUPLOAD,
            [$this->innerRepository, 'update'],
            [
                $file,
            ]
        );
    }

    /**
     * Finds file by id
     *
     * @param  mixed $id File id or array of file ids
     * @return File
     */
    public function find($id)
    {
        return $this->innerRepository->find($id);
    }

    /**
     * @return ArrayCollection
     */
    public function findMany($ids)
    {
        return $this->innerRepository->findMany($ids);
    }

    /**
     * @param FileFinder $finder
     * @return ArrayCollection
     */
    public function findBy(FileFinder $finder)
    {
        return $this->innerRepository->findBy($finder);
    }

    /**
     * @param $uuid
     * @return File
     */
    public function findByUuid($uuid)
    {
        return $this->innerRepository->findByUuid($uuid);
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
        return $this->innerRepository->findByFilename($folder, $filename);
    }

    /**
     * Finds and returns all files
     *
     * @return ArrayCollection
     */
    public function findAll()
    {
        return $this->innerRepository->findAll();
    }

    /**
     * Deletes a file
     *
     * @param File $file
     */
    public function delete(File $file)
    {
        return $this->execute(
            self::COMMAND_DELETE,
            [$this->innerRepository, 'delete'],
            [
                $file,
            ]
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
        return $this->execute(
            self::COMMAND_COPY,
            [$this->innerRepository, 'copy'],
            [
                $file,
                $folder
            ]
        );
    }

    /**
     * @param string $command
     * @param array $callback
     * @param array $params
     * @return mixed
     */
    private function execute($command, $callback, $params)
    {
        $strategy = $this->asynchrony->getStrategy(
            $this->getExecutionStrategy($command)
        );

        return $strategy->execute($callback, $params);
    }
}
