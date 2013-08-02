<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher;

use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\Events;

/**
 * Automatically publishes all files
 *
 * @todo: there are some fucktorings to be made.
 * @todo: ACL integration must be redone after ACL itself has been made a plugin
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
        Events::FILE_BEFORE_DELETE => 'doUnpublish'
    );

    /**
     * @param Publisher $publisher
     */
    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @param FileEvent $event
     */
    public function doPublish(FileEvent $event)
    {
        $this->publisher->publish($event->getFile());
    }

    /**
     * @param FileCopyEvent $event
     */
    public function doUnpublish(FileEvent $event)
    {
        $this->publisher->unpublish($event->getFile());
    }
}
