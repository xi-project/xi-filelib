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
use InvalidArgumentException;
use Xi\Filelib\Configurator;
use Xi\Filelib\Plugin\Image\Command\Command;

/**
 * ImageMagick helper
 *
 * @author pekkis
 */
class ImageMagickHelper
{
    protected $commands = array();
    protected $imageMagickOptions = array();

    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }

    public function addCommand(Command $command)
    {
        $this->commands[] = $command;
    }

    public function getCommands()
    {
        return $this->commands;
    }

    public function setCommands(array $commands = array())
    {
        foreach ($commands as $command) {
            $this->addCommand($command);
        }
    }

    /**
     * Sets ImageMagick options
     *
     * @param array $imageMagickOptions
     */
    public function setImageMagickOptions(array $imageMagickOptions)
    {
        $this->imageMagickOptions = $imageMagickOptions;
    }

    /**
     * Return ImageMagick options
     *
     * @return array
     */
    public function getImageMagickOptions()
    {
        return $this->imageMagickOptions;
    }

    public function execute($img)
    {
        foreach ($this->getImageMagickOptions() as $key => $value) {
            if (!is_array($value)) {
                $value = array($value);
            }

            $method = 'set' . $key;

            call_user_func_array(array($img, $method), $value);
        }

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
     * Creates and returns a command from config array
     *
     * @param  array                     $arr Config array
     * @return Command
     * @throws \InvalidArgumentException
     */
    public function createCommandFromArray($arr)
    {
        if (!is_array($arr) || !isset($arr['type']) || !is_string($arr['type'])) {
            throw new \InvalidArgumentException("Command class missing");
        }

        $className = $arr['type'];
        unset($arr['type']);

        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf(
                "Class '%s' does not exist", $className
            ));
        }

        $command = new $className($arr);

        return $command;
    }
}
