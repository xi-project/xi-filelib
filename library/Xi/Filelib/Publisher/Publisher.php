<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Xi\Filelib\Attacher;
use Xi\Filelib\Event\FileCopyEvent;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\PublisherEvent;
use Xi\Filelib\Events as CoreEvents;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\FilelibException;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\RuntimeException;
use Xi\Filelib\Version;

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
     * @param Version $version
     * @return bool
     */
    public function publishVersion(File $file, Version $version)
    {
        $event = new PublisherEvent($file, array($version->toString()));
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_PUBLISH, $event);

        if ($this->versionPublisher($file, $version)) {
            $this->fileRepository->update($file);
            $event = new PublisherEvent($file, array($version->toString()));
            $this->eventDispatcher->dispatch(Events::FILE_AFTER_PUBLISH, $event);
            return true;
        }

        return false;
    }

    /**
     * @param File $file
     */
    public function publishAllVersions(File $file)
    {
        $versions = $this->getVersionsToPublish($file);

        $event = new PublisherEvent($file, $versions);
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_PUBLISH, $event);

        foreach ($versions as $version) {
            $this->versionPublisher($file, $version);
        }

        $this->fileRepository->update($file);

        $event = new PublisherEvent($file, $versions);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_PUBLISH, $event);
    }

    /**
     * @param File $file
     */
    public function unpublishAllVersions(File $file)
    {
        $versions = $this->getVersionsToUnpublish($file);

        $event = new PublisherEvent($file, $versions);
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_UNPUBLISH, $event);

        foreach ($versions as $version) {
            $this->versionUnpublisher($file, $version);
        }

        $this->fileRepository->update($file);

        $event = new PublisherEvent($file, $versions);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_UNPUBLISH, $event);

        return true;
    }

    /**
     * @param File $file
     * @param Version $version
     * @return bool
     */
    public function unpublishVersion(File $file, Version $version)
    {
        $event = new PublisherEvent($file, array($version));
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_UNPUBLISH, $event);

        if ($this->versionUnpublisher($file, $version)) {
            $this->fileRepository->update($file);

            $event = new PublisherEvent($file, array($version));
            $this->eventDispatcher->dispatch(Events::FILE_AFTER_UNPUBLISH, $event);

            return true;
        }

        return false;
    }

    /**
     * @param File $file
     * @return int
     */
    public function getNumberOfPublishedVersions(File $file)
    {
        return count($this->getPublishedVersions($file));
    }

    /**
     * @param File $file
     * @return array
     */
    public function getPublishedVersions(File $file)
    {
        return array_keys($file->getData()->get('publisher.version_url', array()));
    }

    /**
     * @param File $file
     * @param mixed $version
     * @return bool
     */
    public function isVersionPublished(File $file, $version)
    {
        $version = Version::get($version)->toString();
        return in_array($version, $this->getPublishedVersions($file));
    }

    /**
     * @param File $file
     * @param Version|string $version
     * @return string
     */
    public function getUrl(File $file, $version)
    {
        $version = Version::get($version);

        $versionUrls = $file->getData()->get('publisher.version_url');
        if (isset($versionUrls[$version->toString()])) {
            return $versionUrls[$version->toString()];
        }

        $url = $this->adapter->getUrl(
            $file,
            $version,
            $this->getVersionProvider($file, $version),
            $this->linker
        );
        return $url;
    }

    /**
     * @param string $url
     * @return array Tuple of file and version
     * @throws RuntimeException
     */
    public function reverseUrl($url)
    {
        if (!$this->linker instanceof ReversibleLinker) {
            throw new RuntimeException("Reversible linker is needed to reverse an url");
        }
        return $this->linker->reverseLink($url);
    }


    /**
     * @param FileEvent $event
     */
    public function onBeforeDelete(FileEvent $event)
    {
        $file = $event->getFile();
        $this->unpublishAllVersions($file);
    }

    /**
     * @param FileEvent $event
     */
    public function onBeforeCopy(FileCopyEvent $event)
    {
        $target = $event->getTarget();
        $data = $target->getData();
        $data->delete('publisher.version_url');
    }

    /**
     * @param File $file
     * @return array
     */
    protected function getVersionsToPublish(File $file)
    {
        $ret = $this->profiles->getProfile($file->getProfile())->getFileVersions($file);
        return array_map(
            function ($version) {
                return Version::get($version);
            },
            $ret
        );
    }

    /**
     * @param File $file
     * @return array
     */
    protected function getVersionsToUnpublish(File $file)
    {
        $versionUrls = $file->getData()->get('publisher.version_url', array());
        $ret = array_keys($versionUrls);
        return array_map(
            function ($version) {
                return Version::get($version);
            },
            $ret
        );
    }

    /**
     * @param File $file
     * @param Version $version
     * @return VersionProvider
     */
    protected function getVersionProvider(File $file, Version $version)
    {
        return $this->profiles->getVersionProvider($file, $version);
    }

    /**
     * @param File $file
     * @param Version $version
     * @return bool
     */
    private function versionPublisher(File $file, Version $version)
    {
        $versionUrls = $file->getData()->get('publisher.version_url', array());
        try {
            $this->adapter->publish($file, $version, $this->getVersionProvider($file, $version), $this->linker);
            $versionUrls[$version->toString()] = $this->getUrl($file, $version);
            $file->getData()->set('publisher.version_url', $versionUrls);
            return true;
        } catch (FilelibException $e) {
            return false;
        }
    }

    /**
     * @param File $file
     * @param Version $version
     * @return bool
     */
    private function versionUnpublisher(File $file, Version $version)
    {
        $versionUrls = $file->getData()->get('publisher.version_url', array());
        try {
            $this->adapter->unpublish($file, $version, $this->getVersionProvider($file, $version), $this->linker);
            unset($versionUrls[$version->toString()]);
            $file->getData()->set('publisher.version_url', $versionUrls);
            return true;
        } catch (FilelibException $e) {
            return false;
        }
    }
}
