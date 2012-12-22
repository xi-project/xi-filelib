<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin;

use Xi\Filelib\Configurator;
use Xi\Filelib\Event\FileProfileEvent;

/**
 * Abstract plugin class provides empty convenience methods for all hooks.
 *
 * @author pekkis
 */
abstract class AbstractPlugin implements Plugin
{
    /**
     * @var array Array of profiles
     */
    protected $profiles = array();

    /**
     * @var array Subscribed events
     */
    protected static $subscribedEvents = array(
        'fileprofile.add' => 'onFileProfileAdd',
    );

    /**
     * Returns an array of subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return static::$subscribedEvents;
    }

    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }

    /**
     * Sets the profiles attached to the plugin
     *
     * @param  array          $profiles
     * @return AbstractPlugin
     */
    public function setProfiles(array $profiles)
    {
        $this->profiles = $profiles;

        return $this;
    }

    /**
     * Returns an array of profiles attached to the plugin
     *
     * @return array
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    /**
     * Returns whether plugin belongs to a profile
     *
     * @param  string  $profile
     * @return boolean
     */
    public function hasProfile($profile)
    {
        return in_array($profile, $this->getProfiles());
    }

    public function init()
    { }

    public function onFileProfileAdd(FileProfileEvent $event)
    {
        $profile = $event->getProfile();

        if (in_array($profile->getIdentifier(), $this->getProfiles())) {
            $profile->addPlugin($this);
        }
    }
}
