<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command;

use Pekkis\Queue\Data\AbstractDataSerializer;
use Pekkis\Queue\Data\DataSerializer;
use Xi\Filelib\FileLibrary;

class CommandDataSerializer extends AbstractDataSerializer implements DataSerializer
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    /**
     * @param FileLibrary $filelib
     */
    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }

    /**
     * @param Command $unserialized
     * @return bool
     */
    public function willSerialize($unserialized)
    {
        return ($unserialized instanceof Command);
    }

    /**
     * @param Command $unserialized
     * @return string
     */
    public function serialize($unserialized)
    {
        return serialize($unserialized);
    }

    /**
     * @param string $serialized
     * @return Command
     */
    public function unserialize($serialized)
    {
        /** @var Command $command */
        $command = unserialize($serialized);
        $command->attachTo($this->filelib);

        return $command;
    }
}
