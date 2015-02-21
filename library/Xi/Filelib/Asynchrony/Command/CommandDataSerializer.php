<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony\Command;

use Pekkis\Queue\Data\AbstractDataSerializer;
use Pekkis\Queue\Data\DataSerializer;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Identifiable;

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
        $unserialized->callback[0] = get_class($unserialized->callback[0]);

        $serializedParams = [];
        foreach ($unserialized->params as $key => $param) {

            if (is_scalar($param) || is_array($param)) {
                $serializedParams[$key] = $param;
            } elseif ($param instanceof Identifiable) {
                $serializedParams[$key] = [
                    get_class($param),
                    $param->getId()
                ];
            }
        }

        $unserialized->params = $serializedParams;

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

        var_dump($command);
        die();

        $command->attachTo($this->filelib);

        return $command;
    }
}
