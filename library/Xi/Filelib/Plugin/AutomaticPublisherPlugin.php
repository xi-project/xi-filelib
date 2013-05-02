<?php

namespace Xi\Filelib\Plugin;

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
        'xi_filelib.file.delete' => array('onDelete', -10000),
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
        $this->publish($event->getFile());
    }

    /**
     * @param FileEvent $event
     */
    public function onDelete(FileEvent $event)
    {
        $file = $event->getFile();
        $this->publisher->unpublish($file);
        foreach ($this->getVersions($file) as $version) {
            $this->publisher->unPublishVersion($file, $version);
        }
    }

    /**
     * @param FileCopyEvent $event
     */
    public function onCopy(FileCopyEvent $event)
    {
        $this->publish($event->getTarget());
    }

    /**
     * @param File $file
     */
    protected function publish(File $file)
    {
        $this->publisher->publish($file);
        foreach ($this->getVersions($file) as $version) {
            $this->publisher->publishVersion($file, $version);
        }
    }

    /**
     * @param File $file
     * @return array
     */
    protected function getVersions(File $file)
    {
        return $this->fileOperator->getProfile($file->getProfile())->getFileVersions($file);
    }
}
