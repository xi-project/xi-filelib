<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher;

use Xi\Filelib\Attacher;
use Xi\Filelib\Event\FileCopyEvent;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\File\File;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Events as CoreEvents;
use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\Storage\FileIOException;

/**
 * Publisher
 *
 */
class Publisher implements EventSubscriberInterface, Attacher
{
    /**
     * @var FileRepository
     */
    private $fileRepository;

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

    /**
     * @var ProfileManager
     */
    private $profiles;

    /**
     * @param PublisherAdapter $adapter
     * @param Linker $linker
     */
    public function __construct(PublisherAdapter $adapter, Linker $linker)
    {
        $this->adapter = $adapter;
        $this->linker = $linker;
    }

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->fileRepository = $filelib->getFileRepository();
        $this->profiles = $filelib->getProfileManager();
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->eventDispatcher->addSubscriber($this);
        $this->adapter->attachTo($filelib);
        $this->linker->attachTo($filelib);
    }

    /**
     * @param File $file
     * @return array
     */
    protected function getVersions(File $file)
    {
        return $this->profiles->getProfile($file->getProfile())->getFileVersions($file);
    }

    /**
     * @param File $file
     * @param string $version
     * @return VersionProvider
     */
    protected function getVersionProvider(File $file, $version)
    {
        return $this->profiles->getVersionProvider($file, $version);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            CoreEvents::FILE_BEFORE_DELETE => array('onBeforeDelete'),
            CoreEvents::FILE_BEFORE_COPY => array('onBeforeCopy'),
        );
    }

    /**
     * @param File $file
     */
    public function publish(File $file)
    {
        if ($this->isPublished($file)) {
            return;
        }

        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_PUBLISH, $event);

        $versionUrls = $file->getData()->get('publisher.version_url', array());

        foreach ($this->getVersions($file) as $version) {

            try {
                $this->adapter->publish($file, $version, $this->getVersionProvider($file, $version), $this->linker);
                $versionUrls[$version] = $this->getUrlVersion($file, $version);
            } catch (FileIOException $e) {
                // Version does not exists, but it shall not stop us!
            }

        }

        $file->getData()->set('publisher.published', 1);
        $file->getData()->set('publisher.version_url', $versionUrls);

        $this->fileRepository->update($file);

        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_PUBLISH, $event);
    }

    /**
     * @param File $file
     */
    public function unpublish(File $file)
    {
        if (!$this->isPublished($file)) {
            return;
        }

        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_UNPUBLISH, $event);

        foreach ($this->getVersions($file) as $version) {
            try {
                $this->adapter->unpublish($file, $version, $this->getVersionProvider($file, $version), $this->linker);
            } catch (FileIOException $e) {
                // Version does not exists
            }
        }

        $data = $file->getData();
        $data->set('publisher.published', 0);
        $data->delete('publisher.version_url');

        $this->fileRepository->update($file);

        $event = new FileEvent($file);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_UNPUBLISH, $event);

        $data = $file->getData();
    }

    /**
     * @param File $file
     * @return bool
     */
    public function isPublished(File $file)
    {
        if ($file->getData()->get('publisher.published', 0)) {
            return true;
        }
        return false;
    }

    /**
     * @param File $file
     * @param string $version
     * @return string
     */
    public function getUrlVersion(File $file, $version)
    {
        $versionUrls = $file->getData()->get('publisher.version_url');
        if (isset($versionUrls[$version])) {
            return $versionUrls[$version];
        }

        $url = $this->adapter->getUrlVersion(
            $file,
            $version,
            $this->getVersionProvider($file, $version),
            $this->linker
        );
        return $url;
    }

    /**
     * @param FileEvent $event
     */
    public function onBeforeDelete(FileEvent $event)
    {
        $file = $event->getFile();
        $this->unpublish($file);
    }

    /**
     * @param FileEvent $event
     */
    public function onBeforeCopy(FileCopyEvent $event)
    {
        $target = $event->getTarget();
        $data = $target->getData();

        $data->delete('publisher.published');
        $data->delete('publisher.version_url');
    }
}
