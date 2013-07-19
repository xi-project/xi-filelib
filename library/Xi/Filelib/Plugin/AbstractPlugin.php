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
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Events;

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
        Events::PROFILE_AFTER_ADD => 'onFileProfileAdd',
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

    /**
     * @param array $profiles
     */
    public function setProfiles(array $profiles)
    {
        $this->profiles = $profiles;
        return $this;
    }

    public function onFileProfileAdd(FileProfileEvent $event)
    {
        $profile = $event->getProfile();

        if (in_array($profile->getIdentifier(), $this->getProfiles())) {
            $profile->addPlugin($this);
        }
    }

    public function setDependencies(FileLibrary $filelib)
    {

    }

}
