<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\File\File;
use Xi\Filelib\File\FileOperator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Xi\Filelib\File\FileObject;

class SymfonyRenderer implements Renderer
{

    private $acceleration = false;
        
    private $request;
            
    /**
     * @var FileOperator
     */
    private $fiop;

    /**
     * @var Default options
     */
    private $defaultOptions = array(
        'download' => false,
        'version' => 'original',
    );

    public function __construct(FileOperator $fiop)
    {
        $this->fiop = $fiop;
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
        $provider = $this->fiop->getVersionProvider($file, $options['version']);
        $url = $this->getPublisher()->getUrlVersion($file, $provider);

        return $url;
    }

    /**
     * Renders a file to a response
     *
     * @param File $file File
     * @param array $options Render options
     * @return Response
     */
    public function render(File $file, array $options = array())
    {
        $options = $this->mergeOptions($options);

        $response = new Response();

        if (!$this->getAcl()->isFileReadable($file)) {
            $response->setStatusCode(403);
            $response->setContent(Response::$statusTexts[$response->getStatusCode()]);
            return $response;
        }

        if ($options['version'] === 'original') {
            $res = $this->respondToOriginal($file, $response);
        } else {
            $res = $this->respondToVersion($file, $response, $options['version']);
        }

        // If not 200 swiftly exit here
        if ($response->getStatusCode() !== 200) {
            $response->setContent(Response::$statusTexts[$response->getStatusCode()]);
            return $response;
        }

        if ($options['download'] === true) {
            $response->headers->set('Content-disposition', "attachment; filename={$file->getName()}");
        }

        $this->setContent($response, $res);

        return $response;
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
        return $this->fiop->getPublisher();
    }

    /**
     * Returns Acl
     * 
     * @return Acl
     */
    public function getAcl()
    {
        return $this->fiop->getAcl();
    }

    /**
     * Returns storage
     * 
     * @return Storage
     */
    public function getStorage()
    {
        return $this->fiop->getStorage();
    }

    /**
     * Responds to a original file request and returns path to renderable
     * file if response is 200
     * 
     * @param File $file
     * @param Response $response
     * @return string 
     */
    private function respondToOriginal(File $file, Response $response)
    {
        $profile = $this->fiop->getProfile($file->getProfile());
        if (!$profile->getAccessToOriginal()) {
            $response->setStatusCode(403);
            return;
        }

        $res = $this->getStorage()->retrieve($file);

        return $res;
    }

    /**
     * Responds to a version file request and returns path to renderable
     * file if response is 200
     * 
     * @param File $file
     * @param Response $response
     * @param string Version identifier
     * @return string 
     */
    private function respondToVersion(File $file, Response $response, $version)
    {
        if (!$this->fiop->hasVersion($file, $version)) {
            $response->setStatusCode(404);
            return;
        }

        $provider = $this->fiop->getVersionProvider($file, $version);
        $res = $this->getStorage()->retrieveVersion($file, $provider);

        return $res;
    }

    /**
     * Sets content to response
     */
    private function setContent(Response $response, FileObject $res)
    {
        // @todo: ACCELERATE. IMPLEMENT ZEND RENDERER. FUCKTOR.
        $response->headers->set('Content-Type', $res->getMimetype());
        $content = file_get_contents($res->getPathname());
        $response->setContent($content);
    }
    
    
}

