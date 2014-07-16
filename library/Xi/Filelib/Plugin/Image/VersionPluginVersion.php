<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

use Xi\Filelib\Plugin\Image\Command\Command;

class VersionPluginVersion
{
    /**
     * @var ImageMagickHelper
     */
    protected $helper;

    /**
     * @var string Mime type of version provided
     */
    protected $mimeType;

    /**
     * @var string
     */
    protected $tempDir;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var array
     */
    protected $commands = array();

    /**
     * @param string $identifier
     * @param array $commandDefinitions
     * @param string $mimeType
     */
    public function __construct(
        $identifier,
        array $commandDefinitions = array(),
        $mimeType = null
    ) {
        $this->mimeType = $mimeType;
        $this->identifier = $identifier;

        foreach ($commandDefinitions as $key => $commandDefinition) {
            $this->addCommand(Command::createCommandFromDefinition($key, $commandDefinition));
        }
    }

    /**
     * @return ImageMagickHelper
     */
    public function getHelper($source, $outputDir)
    {
        return new ImageMagickHelper($source, $outputDir, $this->getCommands());
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return null|string
     */
    public function getMimeType()
    {
        return $this->mimeType;
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
     * @param array $definition
     * @return VersionPluginVersion
     */
    public function setCommand($key, Command $command)
    {
        $this->commands[$key] = $command;
        return $this;
    }

    /**
     * @param Command[] $commands
     * @return VersionPluginVersion
     */
    public function setCommands(array $commands)
    {
        $this->commands = $commands;
        return $this;
    }

    /**
     * @param Command $command
     * @return $this
     */
    public function addCommand(Command $command)
    {
        $this->commands[] = $command;
        return $this;
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
