<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Authorization;

use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Plugin\BasePlugin;
use Xi\Filelib\Event\VersionProviderEvent;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\Events as VPEvents;
use Xi\Filelib\Events as CoreEvents;

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

    private $executing = false;

    /**
     * @var array
     */
    protected static $subscribedEvents = [
        VPEvents::VERSIONS_PROVIDED => 'doPublish',
        VPEvents::VERSIONS_DELETED => 'doUnpublish',
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
        if (!$this->adapter->isFileReadableByAnonymous($event->getFile())) {
            return;
        }

        foreach ($event->getVersions() as $version) {
            $this->publisher->publishVersion($event->getFile(), $version);
        }
    }

    /**
     * @param VersionProviderEvent $event
     */
    public function doUnpublish(VersionProviderEvent $event)
    {
        foreach ($event->getVersions() as $version) {
            $this->publisher->unpublishVersion($event->getFile(), $version);
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
