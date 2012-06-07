<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class SymfonyRenderer implements AcceleratedRenderer
{

    /**
     * Server signature regexes and their headers
     *
     * @var array
     */
    static protected $serverSignatures = array(
        '[^nginx]' => 'x-accel-redirect',
        '[^Apache]' => 'x-sendfile',
        '[^lighttpd/(1\.5|2)]' => 'x-sendfile',
        '[^lighttpd/1.4]' => 'x-lighttpd-send-file',
        '[^Cherokee]' => 'x-sendfile',
    );

    /**
     * @var string
     */
    private $accelerationHeader;

    /**
     * @var boolean
     */
    private $accelerationEnabled = false;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var FileLibrary
     */
    private $filelib;

    /**
     * @var Default options
     */
    private $defaultOptions = array(
        'download' => false,
        'version' => 'original',
    );

    /**
     * @var string
     */
    private $stripPrefixFromAcceleratedPath = '';

    /**
     * @var string
     */
    private $addPrefixToAcceleratedPath = '';

    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }

    /**
     *
     * @param string $stripPrefix
     */
    public function setStripPrefixFromAcceleratedPath($stripPrefix)
    {
        $this->stripPrefixFromAcceleratedPath = $stripPrefix;
    }

    public function getStripPrefixFromAcceleratedPath()
    {
        return $this->stripPrefixFromAcceleratedPath;
    }


    public function setAddPrefixToAcceleratedPath($addPrefix)
    {
        $this->addPrefixToAcceleratedPath = $addPrefix;
    }

    public function getAddPrefixToAcceleratedPath()
    {
        return $this->addPrefixToAcceleratedPath;
    }



    /**
     * Sets request context
     *
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
       $this->request = $request;
    }

    /**
     * Returns request context
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     *
     * @return boolean Returns whether response can be accelerated
     */
    public function isAccelerationEnabled()
    {
        return $this->accelerationEnabled;
    }

    /**
     * Enables or disables acceleration
     *
     * @param boolean $flag
     */
    public function enableAcceleration($flag)
    {
        $this->accelerationEnabled = $flag;
    }


    public function isAccelerationPossible()
    {
        // If we have no request as context we cannot accelerate
        if (!$request = $this->getRequest()) {
            return false;
        }

        $serverSignature = $request->server->get('SERVER_SOFTWARE');

        foreach (self::$serverSignatures as $signature => $header) {
            if (preg_match($signature, $serverSignature)) {
                $this->setAccelerationHeader($header);
                return true;
            }
        }

        return false;

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
        return $this->filelib->getPublisher();
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
     * Responds to a original file request and returns path to renderable
     * file if response is 200
     *
     * @param File $file
     * @param Response $response
     * @return string
     */
    private function respondToOriginal(File $file, Response $response)
    {
        $profile = $this->filelib->getFileOperator()->getProfile($file->getProfile());
        if (!$profile->getAccessToOriginal()) {
            $response->setStatusCode(403);
            return;
        }

        $res = $this->getStorage()->retrieve($file->getResource());

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
        if (!$this->filelib->getFileOperator()->hasVersion($file, $version)) {
            $response->setStatusCode(404);
            return;
        }

        $provider = $this->filelib->getFileOperator()->getVersionProvider($file, $version);

        $res = $this->getStorage()->retrieveVersion($file->getResource(), $version, $provider->areSharedVersionsAllowed() ? null : $file);

        return $res;
    }

    /**
     * Sets content to response
     */
    private function setContent(Response $response, FileObject $res)
    {
        $response->headers->set('Content-Type', $res->getMimetype());

        if ($this->isAccelerationEnabled() && $this->isAccelerationPossible()) {
            $this->accelerateResponse($response, $res);
            return;
        }

        $content = file_get_contents($res->getPathname());
        $response->setContent($content);
    }

    /**
     * Sets acceleration header name
     *
     * @param string $header
     */
    private function setAccelerationHeader($header)
    {
        $this->accelerationHeader = $header;
    }

    /**
     * Returns acceleration header name
     *
     * @return string
     */
    private function getAccelerationHeader()
    {
        return $this->accelerationHeader;
    }

    /**
     * Accelerates response
     *
     * @param Response $response
     * @param FileObject $res
     */
    private function accelerateResponse(Response $response, FileObject $res)
    {
        $path = preg_replace("[^{$this->getStripPrefixFromAcceleratedPath()}]", '', $res->getRealPath());
        $path = $this->getAddPrefixToAcceleratedPath() . $path;

        $response->headers->set($this->getAccelerationHeader(), $path);
    }


}

