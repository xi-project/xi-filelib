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
use Xi\Filelib\Event\FileEvent;

use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Acl\Acl;
use Xi\Filelib\File\FileOperator;


abstract class AbstractRenderer implements Renderer
{
    /**
     * @var array Default options
     */
    private $defaultOptions = array(
        'download' => false,
        'version' => 'original',
        'track' => false,
    );

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var Acl
     */
    private $acl;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var FileOperator
     */
    protected $fileOperator;

    /**
     * @param Storage $storage
     * @param Publisher $publisher
     * @param Acl $acl
     * @param EventDispatcherInterface $eventDispatcher
     * @param FileOperator $fileOperator
     */
    public function __construct(
        Storage $storage,
        Publisher $publisher,
        Acl $acl,
        EventDispatcherInterface $eventDispatcher,
        FileOperator $fileOperator
    ) {
        $this->storage = $storage;
        $this->publisher = $publisher;
        $this->acl = $acl;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileOperator = $fileOperator;
    }

    /**
     * Returns url to a file
     *
     * @param  File   $file
     * @param  array $options
     * @return string
     */
    public function getUrl(File $file, $options = array())
    {
        $options = $this->mergeOptions($options);

        if ($options['version'] === 'original') {
            return $this->getPublisher()->getUrl($file);
        }

        // @todo: simplify. Publisher should need the string only!
        $provider = $this->fileOperator->getVersionProvider($file, $options['version']);
        $url = $this->getPublisher()->getUrlVersion($file, $options['version'], $provider);

        return $url;
    }

    /**
     * Merges default options with supplied options
     *
     * @param  array $options
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
    protected function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Returns Acl
     *
     * @return Acl
     */
    protected function getAcl()
    {
        return $this->acl;
    }

    /**
     * Returns storage
     *
     * @return Storage
     */
    protected function getStorage()
    {
        return $this->storage;
    }

    /**
     * Dispatches track event
     *
     * @param File $file
     */
    protected function dispatchTrackEvent(File $file)
    {
        $event = new FileEvent($file);
        $this->getEventDispatcher()->dispatch('xi_filelib.file.render', $event);
    }

}
