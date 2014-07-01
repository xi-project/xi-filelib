<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Authorization;

use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\VersionProviderEvent;
use Xi\Filelib\Events as CoreEvents;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\BasePlugin;
use Xi\Filelib\Plugin\VersionProvider\Events as VPEvents;
use Xi\Filelib\Publisher\Publisher;

/**
 * Automatically publishes all anonymous-readable files, emulating the pre-0.8 behavior
 */
class AutomaticPublisherPlugin extends BasePlugin
{
    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var AuthorizationAdapter
     */
    private $adapter;

    /**
     * @var array
     */
    protected static $subscribedEvents = [
        VPEvents::VERSIONS_PROVIDED => 'doPublish',
        VPEvents::VERSIONS_UNPROVIDED => 'doUnpublish',
        CoreEvents::FILE_AFTER_UPDATE => 'doPermissionsCheck',
    ];

    /**
     * @param Publisher $publisher
     */
    public function __construct(Publisher $publisher, AuthorizationAdapter $adapter)
    {
        $this->publisher = $publisher;
        $this->adapter = $adapter;
    }

    /**
     * @param VersionProviderEvent $event
     */
    public function doPublish(VersionProviderEvent $event)
    {
        $versionable = $event->getVersionable();
        if (!$versionable instanceof File) {
            return;
        }

        if (!$this->adapter->isFileReadableByAnonymous($versionable)) {
            return;
        }

        foreach ($event->getVersions() as $version) {
            $this->publisher->publishVersion($versionable, $version);
        }
    }

    /**
     * @param VersionProviderEvent $event
     */
    public function doUnpublish(VersionProviderEvent $event)
    {
        $versionable = $event->getVersionable();
        if (!$versionable instanceof File) {
            return;
        }

        foreach ($event->getVersions() as $version) {
            $this->publisher->unpublishVersion($versionable, $version);
        }
    }

    /**
     * @param FileEvent $event
     */
    public function doPermissionsCheck(FileEvent $event)
    {
        if ($this->adapter->isFileReadableByAnonymous($event->getFile())) {
            return;
        }
        $this->publisher->unpublishAllVersions($event->getFile());
    }
}
