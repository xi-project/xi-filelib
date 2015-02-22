<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony;

use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository as BaseFileRepository;
use Xi\Filelib\File\FileRepositoryInterface;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\LogicException;

class FileRepository implements FileRepositoryInterface
{
    const COMMAND_UPLOAD = 'xi_filelib.asynchrony.upload';
    const COMMAND_AFTERUPLOAD = 'xi_filelib.asynchrony.afterUpload';
    const COMMAND_UPDATE = 'xi_filelib.asynchrony.update';
    const COMMAND_DELETE = 'xi_filelib.asynchrony.delete';
    const COMMAND_COPY = 'xi_filelib.asynchrony.copy';

    /**
     * @var BaseFileRepository
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
        BaseFileRepository $innerRepository,
        Asynchrony $asynchrony
    ) {
        $this->innerRepository = $innerRepository;
        $this->asynchrony = $asynchrony;

        $this->strategies = [
            self::COMMAND_UPLOAD => ExecutionStrategies::STRATEGY_SYNC,
            self::COMMAND_AFTERUPLOAD => ExecutionStrategies::STRATEGY_SYNC,
            self::COMMAND_UPDATE => ExecutionStrategies::STRATEGY_SYNC,
            self::COMMAND_DELETE => ExecutionStrategies::STRATEGY_SYNC,
            self::COMMAND_COPY => ExecutionStrategies::STRATEGY_SYNC
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
    }

    public function find($id)
    {
        return $this->innerRepository->find($id);
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

    public function __call($method, $params)
    {
        $callback = [$this->innerRepository, $method];
        $strategy = $this->asynchrony->getStrategy(ExecutionStrategies::STRATEGY_SYNC);
        return $strategy->execute($callback, $params);
    }

    private function execute($command, $callback, $params)
    {
        $strategy = $this->asynchrony->getStrategy(
            $this->getExecutionStrategy($command)
        );

        return $strategy->execute($callback, $params);
    }

}