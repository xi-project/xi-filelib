<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Events as CoreEvents;

/**
 * Class Publisher
 *
 * @todo: Event dispatching?
 */
class Publisher implements EventSubscriberInterface
{
    private $fileOperator;

    /**
     * @var Linker
     */
    private $linker;

    /**
     * @var PublisherAdapter
     */
    private $adapter;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(PublisherAdapter $adapter, Linker $linker)
    {
        $this->adapter = $adapter;
        $this->linker = $linker;
    }

    public function attachTo(FileLibrary $filelib)
    {
        $this->fileOperator = $filelib->getFileOperator();
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->adapter->attachTo($filelib);
        $this->linker->attachTo($filelib);
    }

    /**
     * @param File $file
     * @return array
     */
    protected function getVersions(File $file)
    {
        return $this->fileOperator->getProfile($file->getProfile())->getFileVersions($file);
    }

    /**
     * @param File $file
     * @param string $version
     * @return VersionProvider
     */
    protected function getVersionProvider(File $file, $version)
    {
        return $this->fileOperator->getVersionProvider($file, $version);
    }

    public static function getSubscribedEvents()
    {
        return array(
            CoreEvents::FILE_BEFORE_DELETE => array('onBeforeDelete')
        );
    }

    /**
     * @param File $file
     */
    public function publish(File $file)
    {
        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_PUBLISH, $event);

        foreach ($this->getVersions($file) as $version) {
            $this->adapter->publish($file, $version, $this->getVersionProvider($file, $version), $this->linker);
        }

        $data = $file->getData();
        $data['publisher.published'] = 1;

        $this->fileOperator->update($file);

        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_PUBLISH, $event);
    }


    public function unpublish(File $file)
    {
        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_UNPUBLISH, $event);

        foreach ($this->getVersions($file) as $version) {
            $this->adapter->unpublish($file, $version, $this->getVersionProvider($file, $version), $this->linker);
        }

        $data = $file->getData();
        $data['publisher.published'] = 0;

        $this->fileOperator->update($file);

        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_UNPUBLISH, $event);
    }

    public function isPublished(File $file)
    {
        $data = $file->getData();
        if (isset($data['publisher.published']) && $data['publisher.published'] == 1) {
            return true;
        }
        return false;
    }


    public function getUrlVersion(File $file, $version)
    {
        return $this->adapter->getUrlVersion($file, $version, $this->getVersionProvider($file, $version), $this->linker);
    }

    /**
     * @param FileEvent $event
     */
    public function onBeforeDelete(FileEvent $event)
    {
        $file = $event->getFile();
        $this->unpublish($file);
    }
}
