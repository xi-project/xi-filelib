<?php

namespace Xi\Filelib\Publisher;

use Doctrine\Common\EventSubscriber;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\FileProfile;
use Xi\Filelib\File\File;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;

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


    public function __construct(FileLibrary $filelib, PublisherAdapter $adapter, Linker $linker)
    {
        $adapter->setDependencies($filelib);
        $this->adapter = $adapter;

        $this->linker = $linker;

        $this->fileOperator = $filelib->getFileOperator();
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
            'xi_filelib.file.delete' => array('onDelete')
        );
    }

    /**
     * @param File $file
     */
    public function publish(File $file)
    {
        $this->adapter->publish($file, $this->linker);
        foreach ($this->getVersions($file) as $version) {
            $this->adapter->publishVersion($file, $this->getVersionProvider($file, $version), $this->linker);
        }

        $data = $file->getData();
        $data['publisher.published'] = 1;
    }


    public function unpublish(File $file)
    {
        $this->adapter->unpublish($file, $this->linker);
        foreach ($this->getVersions($file) as $version) {
            $this->adapter->unPublishVersion($file, $this->getVersionProvider($file, $version), $this->linker);
        }

        $data = $file->getData();
        $data['publisher.published'] = 0;
    }


    public function getUrl(File $file)
    {
        return $this->adapter->getUrl($file, $this->linker);
    }

    public function getUrlVersion(File $file, $version)
    {
        return $this->adapter->getUrl($file, $this->getVersionProvider($file, $version), $this->linker);
    }

    /**
     * @param FileEvent $event
     */
    public function onDelete(FileEvent $event)
    {
        $file = $event->getFile();
        $this->unpublish($file);
    }
}
