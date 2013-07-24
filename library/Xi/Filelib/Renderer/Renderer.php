<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Authorization\AccessDeniedException;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Storage\Storage;

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
    private $adapter;

    /**
     * @var FileOperator
     */
    private $fileOperator;

    /**
     * @var Storage
     */
    private $storage;

    public function __construct(FileLibrary $filelib, RendererAdapter $adapter)
    {
        $this->adapter = $adapter;
        $this->fileOperator = $filelib->getFileOperator();
        $this->storage = $filelib->getStorage();
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

        $options = $this->mergeOptions($options);

        if (!$this->fileOperator->hasVersion($file, $version)) {
            return $this->adapter->returnResponse($response->setStatusCode(404));
        }

        if ($options['download'] == true) {
            $response->setHeader('Content-disposition', "attachment; filename={$file->getName()}");
        }

        $storage = $this->storage;

        $retrieved = new FileObject($storage->retrieveVersion($file->getResource(), $version));
        $response->setHeader('Content-Type', $retrieved->getMimetype());
        $response->setContent(function () use ($retrieved) {
            return $retrieved->fpassthru();
        });

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


    /**
     * Responds to a version file request and returns path to renderable
     * file if response is 200
     *
     * @param File     $file
     * @param Response $response
     * @param string Version identifier
     * @return string
     */
    private function respondToVersion(File $file, Response $response, $version)
    {
        $provider = $this->fileOperator->getVersionProvider($file, $version);

        $res = $this->getStorage()->retrieveVersion($file->getResource(), $version, $provider->areSharedVersionsAllowed() ? null : $file);

        return $res;
    }


}
