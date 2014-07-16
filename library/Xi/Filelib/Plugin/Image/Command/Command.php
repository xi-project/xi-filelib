<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image\Command;

use Imagick;
use Xi\Filelib\Plugin\Image\ImageMagickHelper;


/**
 * @author pekkis
 */
abstract class Command
{
    /**
     * @var ImageMagickHelper
     */
    protected $helper;

    /**
     * @param ImageMagickHelper $helper
     * @return Command
     */
    public function setHelper(ImageMagickHelper $helper)
    {
        $this->helper = $helper;
        return $this;
    }

    /**
     * Executes command
     *
     * @param Imagick $imagick
     */
    abstract public function execute(Imagick $imagick);

    /**
     * Creates a command from array definition
     *
     * @param string $key
     * @param array $definition
     * @return Command
     */
    public static function createCommandFromDefinition($key, $definition)
    {
        if ($definition instanceof Command) {
            return $definition;
        }

        $commandClass = (is_numeric($key)) ? 'Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand' : $key;
        $reflClass = new \ReflectionClass($commandClass);
        $command = $reflClass->newInstanceArgs($definition);

        return $command;
    }

    /**
     * @param array $definitions
     * @return Command[]
     */
    public static function createCommandsFromDefinitions(array $definitions)
    {
        $commands = [];
        foreach ($definitions as $key => $definition) {
            $commands[] = self::createCommandFromDefinition($key, $definition);
        }

        return $commands;
    }
}
