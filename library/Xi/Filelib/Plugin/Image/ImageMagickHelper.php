<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

use Imagick;
use ImagickException;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\Plugin\Image\Command\Command;

/**
 * ImageMagick helper
 *
 * @author pekkis
 */
class ImageMagickHelper
{
    protected $commands = array();

    public function __construct($commandDefinitions = array())
    {
        foreach ($commandDefinitions as $key => $definition) {
            $this->addCommand($this->createCommandFromDefinition($key, $definition));
        }
    }

    /**
     * @param Command $command
     */
    public function addCommand(Command $command)
    {
        $this->commands[] = $command;
    }

    /**
     * @return Command[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @param $key
     * @return Command
     */
    public function getCommand($key)
    {
        return $this->commands[$key];
    }

    public function setCommand($key, $command)
    {
        $this->commands[$key] = $command;
    }


    public function execute($img)
    {
        foreach ($this->getCommands() as $command) {
            $command->execute($img);
        }
    }

    /**
     * Creates a new imagick resource from path
     *
     * @param  string                   $path Image path
     * @return Imagick
     * @throws InvalidArgumentException
     */
    public function createImagick($path)
    {
        try {
            return new Imagick($path);
        } catch (ImagickException $e) {
            throw new InvalidArgumentException(
                sprintf("ImageMagick could not be created from path '%s'", $path),
                500,
                $e
            );
        }
    }

    /**
     * @param mixed $key
     * @param mixed $definition
     */
    private function createCommandFromDefinition($key, $definition)
    {
        if ($definition instanceof Command) {
            return $definition;
        }

        $commandClass = (is_numeric($key)) ? 'Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand' : $key;
        $reflClass = new \ReflectionClass($commandClass);
        $command = $reflClass->newInstanceArgs($definition);

        return $command;
    }
}
