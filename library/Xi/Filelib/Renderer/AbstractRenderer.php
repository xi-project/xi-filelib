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
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Events;

abstract class AbstractRenderer implements Renderer
{
    /**
     * @var array Default options
     */
    private $defaultOptions = array(
        'download' => false,
        'version' => 'original',
    );

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var FileOperator
     */
    protected $fileOperator;

    public function __construct(
        FileLibrary $filelib
    ) {
        $this->storage = $filelib->getStorage();
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->fileOperator = $filelib->getFileOperator();
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
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->eventDispatcher;
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
     * @param File $file
     */
    protected function dispatchRenderEvent(File $file)
    {
        $event = new FileEvent($file);
        $this->getEventDispatcher()->dispatch(Events::FILE_AFTER_RENDER, $event);
    }
}
