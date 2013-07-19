<?php

namespace Xi\Filelib\Publisher;

use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\Event\FileCopyEvent;

/**
 * Automatically publishes all files
 *
 * @todo: there are some fucktorings to be made.
 * @todo: ACL integration must be redone after ACL itself has been made a plugin
 */
class AutomaticPublisherPlugin extends AbstractPlugin
{
    /**
     * @var array
     */
    protected static $subscribedEvents = array(
        'xi_filelib.file.after_upload' => array('onAfterUpload', -10000),
        'xi_filelib.file.copy' => array('onCopy', -10000),
    );

    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var FileOperator
     */
    private $fileOperator;

    /**
     * @param Publisher $publisher
     */
    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @param FileLibrary $filelib
     */
    public function setDependencies(FileLibrary $filelib)
    {
        $this->fileOperator = $filelib->getFileOperator();
    }

    /**
     * @param FileEvent $event
     */
    public function onAfterUpload(FileEvent $event)
    {
        $this->publisher->publish($event->getFile());
    }

    /**
     * @param FileCopyEvent $event
     */
    public function onCopy(FileCopyEvent $event)
    {
        $this->publisher->publish($event->getTarget());
    }

}
