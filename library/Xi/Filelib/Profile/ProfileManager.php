<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Profile;

use Xi\Collections\Collection\ArrayCollection;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\Version;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Event\FileProfileEvent;
use Xi\Filelib\Events;

class ProfileManager
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Returns whether a file has a certain version
     *
     * @param File $file
     * @param Version $version
     * @return boolean
     */
    public function hasVersion(File $file, Version $version)
    {
        return $this->getProfile($file->getProfile())->fileHasVersion($file, $version);
    }

    /**
     * Returns version provider for a file/version
     *
     * @param File $file
     * @param Version $version
     * @return VersionProvider
     */
    public function getVersionProvider(File $file, Version $version)
    {
        return $this->getProfile($file->getProfile())->getVersionProvider($file, $version);
    }

    /**
     * Adds a profile
     *
     * @param FileProfile $profile
     * @return ProfileManager
     * @throws InvalidArgumentException
     */
    public function addProfile(FileProfile $profile)
    {
        $identifier = $profile->getIdentifier();

        if (isset($this->profiles[$identifier])) {
            throw new InvalidArgumentException("Profile '{$identifier}' already exists");
        }

        $this->profiles[$identifier] = $profile;
        $this->eventDispatcher->addSubscriber($profile);

        $event = new FileProfileEvent($profile);
        $this->eventDispatcher->dispatch(Events::PROFILE_AFTER_ADD, $event);

        return $this;
    }

    /**
     * Returns a file profile
     *
     * @param  string                   $identifier File profile identifier
     * @throws InvalidArgumentException
     * @return FileProfile
     */
    public function getProfile($identifier)
    {
        if (!isset($this->profiles[$identifier])) {
            throw new InvalidArgumentException("File profile '{$identifier}' not found");
        }

        return $this->profiles[$identifier];
    }

    /**
     * Returns all file profiles
     *
     * @return ArrayCollection
     */
    public function getProfiles()
    {
        return ArrayCollection::create($this->profiles);
    }

    /**
     * @var array Profiles
     */
    private $profiles = array();
}
