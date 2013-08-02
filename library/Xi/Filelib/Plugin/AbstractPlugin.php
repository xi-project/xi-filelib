<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin;

use Xi\Filelib\Event\FileProfileEvent;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Events;
use Xi\Filelib\InvalidArgumentException;

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
    protected static $subscribedEvents = array();

    /**
     * @var callable
     */
    private $resolverFunc;

    /**
     * Returns an array of subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array_merge(
            static::$subscribedEvents,
            array(Events::PROFILE_AFTER_ADD => 'onFileProfileAdd')
        );
    }

    /**
     * Returns whether plugin belongs to a profile
     *
     * @param  string  $profile
     * @return boolean
     */
    public function hasProfile($profile)
    {
        if (!$this->resolverFunc) {
            return false;
        }
        return call_user_func($this->resolverFunc, $profile);
    }

    public function onFileProfileAdd(FileProfileEvent $event)
    {
        $profile = $event->getProfile();

        if ($this->hasProfile($profile->getIdentifier())) {
            $profile->addPlugin($this);
        }
    }

    public function attachTo(FileLibrary $filelib)
    {

    }

    public function setHasProfileResolver($resolverFunc)
    {
        if (!is_callable($resolverFunc)) {
            throw new InvalidArgumentException("Resolver must be a callable");
        }

        $this->resolverFunc = $resolverFunc;
    }

    /**
     * @param array $profiles
     */
    public function setProfiles(array $profiles)
    {
        $this->setHasProfileResolver(
            function ($profile) use ($profiles) {
                return (bool) in_array($profile, $profiles);
            }
        );
    }
}
