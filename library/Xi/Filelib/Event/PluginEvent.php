<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\Plugin;

/**
 * Plugin event
 */
class PluginEvent extends Event
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var FileLibrary
     */
    private $filelib;

    public function __construct(Plugin $plugin, FileLibrary $filelib)
    {
        $this->plugin = $plugin;
        $this->filelib = $filelib;
    }

    /**
     * Returns plugin
     *
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->filelib;
    }
}
