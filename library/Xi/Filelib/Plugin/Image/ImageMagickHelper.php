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
use Pekkis\TemporaryFileManager\TemporaryFileManager;
use Xi\Filelib\Plugin\Image\Command\Command;
use Xi\Filelib\RuntimeException;

class ImageMagickHelper
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var TemporaryFileManager
     */
    private $outputDir;

    /**
     * @var array
     */
    private $commands = array();

    /**
     * @var bool
     */
    private $isExecuted = false;

    /**
     * @param string $source
     * @param TemporaryFileManager $outputDir
     * @param Command[] $commands
     */
    public function __construct($source, TemporaryFileManager $outputDir, $commands = array())
    {
        $this->source = $source;
        $this->outputDir = $outputDir;
        foreach ($commands as $command) {
            $this->addCommand($command);
        }
    }

    /**
     * @return bool
     */
    public function isExecuted()
    {
        return $this->isExecuted;
    }

    /**
     * @param Command $command
     */
    public function addCommand(Command $command)
    {
        $this->commands[] = $command;
        $command->setHelper($this);
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

    /**
     * @param $key
     * @param Command $command
     */
    public function setCommand($key, Command $command)
    {
        $this->commands[$key] = $command;
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function execute()
    {
        if ($this->isExecuted()) {
            throw new RuntimeException('Helper already executed');
        }

        $img = $this->createImagick();



        foreach ($this->getCommands() as $command) {
            $command->execute($img);
        }

        $this->isExecuted = true;

        return $this->outputDir->add($img->getImageBlob());
    }

    /**
     * @return Imagick
     * @throws RuntimeException
     */
    private function createImagick()
    {
        try {
            return new Imagick($this->source);
        } catch (ImagickException $e) {
            throw new RuntimeException(
                sprintf("ImageMagick could not be created from path '%s'", $this->source),
                500,
                $e
            );
        }
    }
}
