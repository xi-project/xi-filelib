<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Authorization;

use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\File\File;
use Xi\Filelib\Events as CoreEvents;

/**
 * Automatically publishes all anonymous-readable files, emulating the pre-0.8 behavior
 */
class AutomaticPublisherPlugin extends AbstractPlugin
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
    protected static $subscribedEvents = array(
        CoreEvents::FILE_AFTER_AFTERUPLOAD => 'doPublish',
        CoreEvents::FILE_BEFORE_UPDATE => 'doUnpublish',
        CoreEvents::FILE_AFTER_UPDATE => 'doPublish',
    );

    /**
     * @param Publisher $publisher
     */
    public function __construct(Publisher $publisher, AuthorizationAdapter $adapter)
    {
        $this->publisher = $publisher;
        $this->adapter = $adapter;
    }

    /**
     * @param FileEvent $event
     */
    public function doPublish(FileEvent $event)
    {
        if ($this->executing) {
            return;
        }

        if (!$this->adapter->isFileReadableByAnonymous($event->getFile())) {
            return;
        }

        $this->executing = true;
        $this->publisher->publish($event->getFile());
        $this->executing = false;
    }

    /**
     * @param FileEvent $event
     */
    public function doUnpublish(FileEvent $event)
    {
        if ($this->executing) {
            return;
        }

        $this->executing = true;
        $this->publisher->unpublish($event->getFile());
        $this->executing = false;
    }
}
