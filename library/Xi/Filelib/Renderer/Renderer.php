<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Authorization\AccessDeniedException;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Storage\Storage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Event\FileEvent;

class Renderer
{
    /**
     * @var array Default options
     */
    private $defaultOptions = array(
        'download' => false,
    );

    /**
     * @var RendererAdapter
     */
    protected $adapter;

    /**
     * @var FileOperator
     */
    private $fileOperator;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(FileLibrary $filelib, RendererAdapter $adapter)
    {
        $this->adapter = $adapter;
        $this->fileOperator = $filelib->getFileOperator();
        $this->storage = $filelib->getStorage();
        $this->eventDispatcher = $filelib->getEventDispatcher();
    }

    /**
     * Renders a file to a response
     *
     * @param mixed $file
     * @param string $version
     * @param array $options
     * @return mixed
     */
    public function render($file, $version, array $options = array())
    {
        $response = new Response();

        if (!$file instanceof File) {
            $file = $this->fileOperator->find($file);

            if (!$file) {
                return $this->adapter->returnResponse($response->setStatusCode(404));
            }
        }

        // Supports authorization component
        try {

            $event = new FileEvent($file);
            $this->eventDispatcher->dispatch(Events::RENDERER_BEFORE_RENDER, $event);

        } catch (AccessDeniedException $e) {
            return $this->adapter->returnResponse($response->setStatusCode(403));
        }



        $options = $this->mergeOptions($options);

        if (!$this->fileOperator->hasVersion($file, $version)) {
            return $this->adapter->returnResponse($response->setStatusCode(404));
        }

        if ($options['download'] == true) {
            $response->setHeader('Content-disposition', "attachment; filename={$file->getName()}");
        }

        $retrieved = new FileObject($this->retrieve($file, $version));
        $response->setHeader('Content-Type', $retrieved->getMimetype());

        $this->injectContentToResponse($retrieved, $response);
        return $this->adapter->returnResponse($response);
    }


    /**
     * Merges default options with supplied options
     *
     * @param  array $options
     * @return array
     */
    private function mergeOptions(array $options)
    {
        return array_merge($this->defaultOptions, $options);
    }

    private function retrieve(File $file, $version)
    {
        $provider = $this->fileOperator->getVersionProvider($file, $version);
        $res = $this->storage->retrieveVersion($file->getResource(), $version, $provider->areSharedVersionsAllowed() ? null : $file);
        return $res;
    }

    protected function injectContentToResponse(FileObject $file, Response $response)
    {
        $response->setContent(function () use ($file) {
            return file_get_contents($file->getRealPath());
        });
    }

}
