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
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\Events;

/**
 * Automatically publishes all files, emulating the pre-0.8 behavior
 *
 */
class AutomaticPublisherPlugin extends AbstractPlugin
{
    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var array
     */
    protected static $subscribedEvents = array(
        Events::FILE_AFTER_AFTERUPLOAD => 'doPublish',
        Events::FILE_BEFORE_UPDATE => 'doUnpublishAndPublish',
        Events::FILE_BEFORE_DELETE => 'doUnpublish'
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
        $this->publisher->publish($event->getFile());
    }

    /**
     * @param FileEvent $event
     */
    public function doUnpublish(FileEvent $event)
    {
        $file = $event->getFile();

        if ($this->publisher->isPublished($file)) {
            $this->publisher->unpublish($file);
        }

    }

    /**
     * @param FileEvent $event
     */
    public function doUnPublishAndPublish(FileEvent $event)
    {
        $file = $event->getFile();
        $this->publisher->unpublish($file);
        $this->publisher->publish($file);
    }


}
