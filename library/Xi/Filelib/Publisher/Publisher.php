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
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Events;

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
            Events::FILE_BEFORE_DELETE => array('onBeforeDelete')
        );
    }

    /**
     * @param File $file
     */
    public function publish(File $file)
    {
        // $this->adapter->publish($file, $this->linker);
        foreach ($this->getVersions($file) as $version) {
            $this->adapter->publishVersion($file, $this->getVersionProvider($file, $version), $this->linker);
        }

        $data = $file->getData();
        $data['publisher.published'] = 1;

        $this->fileOperator->update($file);
    }


    public function unpublish(File $file)
    {
        // $this->adapter->unpublish($file, $this->linker);
        foreach ($this->getVersions($file) as $version) {
            $this->adapter->unPublishVersion($file, $this->getVersionProvider($file, $version), $this->linker);
        }

        $data = $file->getData();
        $data['publisher.published'] = 0;

        $this->fileOperator->update($file);
    }


    public function getUrl(File $file)
    {
        return $this->adapter->getUrl($file, $this->linker);
    }

    public function getUrlVersion(File $file, $version)
    {
        return $this->adapter->getUrlVersion($file, $this->getVersionProvider($file, $version), $this->linker);
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
