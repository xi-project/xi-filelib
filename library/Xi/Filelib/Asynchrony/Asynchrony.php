<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony;

use Xi\Filelib\Asynchrony\ExecutionStrategy\ExecutionStrategy;
use Xi\Filelib\Asynchrony\ExecutionStrategy\SynchronousExecutionStrategy;
use Xi\Filelib\Asynchrony\Serializer\DataSerializer\RepositorySerializer;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\LogicException;

class Asynchrony
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    /**
     * @var array
     */
    private $strategies = [];

    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;

        $filelib->setFileRepository(
            new FileRepository(
                $filelib->getFileRepository(),
                $this
            )
        );

        $this->addStrategy(
            new SynchronousExecutionStrategy()
        );
    }

    public function addStrategy(ExecutionStrategy $strategy)
    {
        if (isset($this->strategies[$strategy->getIdentifier()])) {
            throw new LogicException(
                sprintf(
                    "Strategy '%s' already exists",
                    $strategy->getIdentifier()
                )
            );
        }
        $strategy->attachTo($this->filelib);
        $this->strategies[$strategy->getIdentifier()] = $strategy;
    }

    /**
     * @param string $identifier
     * @return ExecutionStrategy
     * @throws LogicException
     */
    public function getStrategy($identifier)
    {
        if (!isset($this->strategies[$identifier])) {
            throw new LogicException(
                sprintf(
                    "Strategy '%s' does not exist",
                    $identifier
                )
            );
        }

        return $this->strategies[$identifier];
    }

}
