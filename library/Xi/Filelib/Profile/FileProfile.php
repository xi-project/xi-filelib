<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Profile;

use Xi\Filelib\Plugin\Plugin;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\File\File;
use Xi\Filelib\Event\PluginEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\Events;

class FileProfile implements EventSubscriberInterface
{
    /**
     * @var array Subscribed events
     */
    protected static $subscribedEvents = array(
        Events::PLUGIN_AFTER_ADD => 'onPluginAdd'
    );

    /**
     * @var array Versions for file types
     */
    private $fileVersions = array();

    /**
     * @var string Machine readable identifier
     */
    private $identifier;

    /**
     * @var array Array of plugins
     */
    private $plugins = array();

    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Returns array of subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return static::$subscribedEvents;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Adds a plugin
     *
     * @param  Plugin      $plugin
     * @return FileProfile
     */
    public function addPlugin(Plugin $plugin)
    {
        $this->plugins[] = $plugin;

        if ($plugin instanceof VersionProvider) {
            foreach ($plugin->getVersions() as $version) {
                $this->addFileVersion($version, $plugin);
            }
        }
        return $this;
    }

    /**
     * Returns all plugins
     *
     * @return array Array of plugins
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * Adds a file version
     *
     * @param  string          $versionIdentifier Version identifier
     * @param  VersionProvider $versionProvider   Version provider
     * @return FileProfile
     */
    public function addFileVersion(
        $versionIdentifier,
        VersionProvider $versionProvider
    ) {
        $this->fileVersions[$versionIdentifier] = $versionProvider;
        return $this;
    }

    /**
     * Returns all defined versions of a file
     *
     * @param  File  $file File item
     * @return array Array of provided versions
     */
    public function getFileVersions(File $file)
    {
        $ret = array();
        foreach ($this->fileVersions as $version => $versionProvider) {
            /** @var VersionProvider $versionProvider */
            if ($versionProvider->providesFor($file)) {
                $ret[] = $version;
            }
        }
        return $ret;
    }

    /**
     * Returns whether a file has a certain version
     *
     * @param  File    $file    File item
     * @param  string  $version Version
     * @return boolean
     */
    public function fileHasVersion(File $file, $version)
    {
        return in_array($version, $this->getFileVersions($file));
    }

    /**
     * Returns version provider for a file/version
     *
     * @param  File                     $file    File item
     * @param  string                   $version Version
     * @return VersionProvider          Provider
     * @throws InvalidArgumentException
     */
    public function getVersionProvider(File $file, $version)
    {
        if (!$this->fileHasVersion($file, $version)) {
            throw new InvalidArgumentException("File has no version '{$version}'");
        }
        return $this->fileVersions[$version];
    }

    /**
     * Fires on plugin.add event. Adds plugin if plugin has profile.
     *
     * @param PluginEvent $event
     */
    public function onPluginAdd(PluginEvent $event)
    {
        $plugin = $event->getPlugin();

        if ($plugin->hasProfile($this->getIdentifier())) {
            $this->addPlugin($plugin);
        }
    }

    /**
     * Returns whether profile allows shared resources for a file
     *
     * @param  File    $file
     * @return boolean
     */
    public function isSharedResourceAllowed(File $file)
    {
        foreach ($this->getPlugins() as $plugin) {
            if ($plugin instanceof VersionProvider
                && $plugin->providesFor($file)
                && !$plugin->isSharedResourceAllowed()
            ) {
                return false;
            }
        }

        return true;
    }
}
