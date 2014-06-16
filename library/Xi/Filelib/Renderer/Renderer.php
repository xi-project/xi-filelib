<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\Event\RenderEvent;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Authorization\AccessDeniedException;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Plugin\VersionProvider\LazyVersionProvider;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Profile\ProfileManager;
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
     * @var FileRepository
     */
    private $fileRepository;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ProfileManager
     */
    private $profiles;

    public function __construct(FileLibrary $filelib, RendererAdapter $adapter)
    {
        $this->adapter = $adapter;
        $this->fileRepository = $filelib->getFileRepository();
        $this->profiles = $filelib->getProfileManager();
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
            $file = $this->fileRepository->find($file);

            if (!$file) {
                return $this->adaptResponse(
                    null,
                    $version,
                    $response->setStatusCode(404)
                );
            }
        }

        // Authorization component support
        try {
            $event = new FileEvent($file);
            $this->eventDispatcher->dispatch(Events::RENDERER_BEFORE_RENDER, $event);
        } catch (AccessDeniedException $e) {
            return $this->adaptResponse(
                $file,
                $version,
                $response->setStatusCode(403)
            );
        }

        $options = $this->mergeOptions($options);

        if (!$this->profiles->hasVersion($file, $version)) {
            return $this->adaptResponse(
                $file,
                $version,
                $response->setStatusCode(404)
            );
        }

        if ($options['download'] == true) {
            $response->setHeader('Content-disposition', "attachment; filename={$file->getName()}");
        }


        $retrieved = new FileObject($this->retrieve($file, $version));
        $response->setHeader('Content-Type', $retrieved->getMimetype());

        $this->injectContentToResponse($retrieved, $response);
        return $this->adaptResponse(
            $file,
            $version,
            $response
        );
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
        /** @var VersionProvider $provider */
        $provider = $this->profiles->getVersionProvider($file, $version);
        $versionable = $provider->getApplicableStorable($file);
        if ($provider instanceof LazyVersionProvider) {
            if (!$versionable->hasVersion($version)) {
                $provider->createProvidedVersions($file);
            }
        }

        return $this->storage->retrieveVersion($versionable, $version);
    }

    protected function injectContentToResponse(FileObject $file, Response $response)
    {
        $response->setContent(
            function () use ($file) {
                return file_get_contents($file->getRealPath());
            }
        );
    }

    /**
     * @param File $file
     * @param string $version
     * @param Response $response
     * @return mixed
     */
    protected function adaptResponse(File $file = null, $version, Response $response)
    {
        $adaptedResponse = $this->adapter->adaptResponse($response);

        $event = new RenderEvent($file, $version, $response, $adaptedResponse);
        $this->eventDispatcher->dispatch(Events::RENDERER_RENDER, $event);

        return $adaptedResponse;
    }

}
