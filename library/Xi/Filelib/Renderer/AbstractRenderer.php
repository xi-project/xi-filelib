<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Renderer;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Event\FileEvent;

abstract class AbstractRenderer
{

    /**
     * @var Default options
     */
    private $defaultOptions = array(
        'download' => false,
        'version' => 'original',
        'track' => false,
    );

    /**
     * @var FileLibrary
     */
    protected $filelib;

    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }

    /**
     * Returns url to a file
     *
     * @param File $file
     * @param type $options
     * @return string
     */
    public function getUrl(File $file, $options = array())
    {
        $options = $this->mergeOptions($options);

        if ($options['version'] === 'original') {
            return $this->getPublisher()->getUrl($file);
        }

        // @todo: simplify. Publisher should need the string only!
        $provider = $this->filelib->getFileOperator()->getVersionProvider($file, $options['version']);
        $url = $this->getPublisher()->getUrlVersion($file, $options['version'], $provider);

        return $url;
    }


    /**
     * Merges default options with supplied options
     *
     * @param array $options
     * @return array
     */
    public function mergeOptions(array $options)
    {
        return array_merge($this->defaultOptions, $options);
    }

    /**
     * Returns publisher
     *
     * @return Publisher
     */
    public function getPublisher()
    {
        return $this->filelib->getPublisher();
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->filelib->getEventDispatcher();
    }

    /**
     * Returns Acl
     *
     * @return Acl
     */
    public function getAcl()
    {
        return $this->filelib->getAcl();
    }

    /**
     * Returns storage
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->filelib->getStorage();
    }

    /**
     * Dispatches track event
     *
     * @param File $file
     */
    protected function dispatchTrackEvent(File $file)
    {
        $event = new FileEvent($file);
        $this->getEventDispatcher()->dispatch('file.render', $event);
    }


}
