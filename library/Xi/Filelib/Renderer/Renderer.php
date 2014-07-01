<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\Event\RenderEvent;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Authorization\AccessDeniedException;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\InvalidVersionException;
use Xi\Filelib\Plugin\VersionProvider\LazyVersionProvider;
use Xi\Filelib\Version;
use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\Storage\Storage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\FilelibException;

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

    /**
     * @param FileLibrary $filelib
     * @param RendererAdapter $adapter
     */
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
     * @param mixed $version Version string or object
     * @param array $options
     * @return mixed
     */
    public function render($file, $version, array $options = array())
    {
        $version = Version::get($version);
        $response = new Response();

        if (!$file instanceof File) {
            $file = $this->fileRepository->find($file);

            if (!$file) {
                return $this->adaptResponse(
                    $response->setStatusCode(404),
                    $version,
                    null
                );
            }
        }

        // Renderer / authorization evil tag team
        try {
            $event = new FileEvent($file);
            $this->eventDispatcher->dispatch(Events::RENDERER_BEFORE_RENDER, $event);
        } catch (AccessDeniedException $e) {
            return $this->adaptResponse(
                $response->setStatusCode(403),
                $version,
                $file
            );
        }

        try {
            $provider = $this->profiles->getVersionProvider($file, $version);
            $version = $provider->ensureValidVersion($version);
        } catch (InvalidVersionException $e) {
            return $this->adaptResponse(
                $response->setStatusCode(404),
                $version,
                null
            );
        }

        if (!$this->versionIsObtainable($file, $version)) {
            return $this->adaptResponse(
                $response->setStatusCode(404),
                $version,
                $file
            );
        }

        $options = $this->mergeOptions($options);

        if ($options['download'] == true) {
            $response->setHeader('Content-disposition', "attachment; filename={$file->getName()}");
        }
        $retrieved = new FileObject($this->retrieve($file, $version));
        $response->setHeader('Content-Type', $retrieved->getMimetype());

        $this->injectContentToResponse($retrieved, $response);
        return $this->adaptResponse(
            $response,
            $version,
            $file
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

    /**
     * Defines whether a version is obtainable by some means to render it
     *
     * @param File $file
     * @param Version $version
     * @return bool
     */
    protected function versionIsObtainable(File $file, Version $version)
    {
        $provider = $this->profiles->getVersionProvider($file, $version);
        $versionable = $provider->getApplicableVersionable($file);

        if ($versionable->hasVersion($version)) {
            return true;
        }

        if (!$provider instanceof LazyVersionProvider) {
            return false;
        }

        try {
            $provider->provideVersion($file, $version);
            return true;
        } catch (FilelibException $e) {
            return false;
        }
    }

    /**
     * @param File $file
     * @param Version $version
     * @return string
     */
    private function retrieve(File $file, Version $version)
    {
        return $this->storage->retrieveVersion(
            $this->profiles->getVersionProvider($file, $version)->getApplicableVersionable($file),
            $version
        );
    }

    /**
     * @param FileObject $file
     * @param Response $response
     */
    protected function injectContentToResponse(FileObject $file, Response $response)
    {
        $response->setContent(
            function () use ($file) {
                return file_get_contents($file->getRealPath());
            }
        );
    }

    /**
     * @param Response $response
     * @param Version $version
     * @param File $file
     * @return mixed
     */
    protected function adaptResponse(Response $response, Version $version, File $file = null)
    {
        $adaptedResponse = $this->adapter->adaptResponse($response);

        $event = new RenderEvent($response, $adaptedResponse, $version, $file);
        $this->eventDispatcher->dispatch(Events::RENDERER_RENDER, $event);

        return $adaptedResponse;
    }
}
