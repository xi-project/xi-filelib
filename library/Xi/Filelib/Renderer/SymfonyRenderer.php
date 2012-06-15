<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Event\FileEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SymfonyRenderer extends AbstractAcceleratedRenderer implements AcceleratedRenderer
{

    /**
     * @var Request
     */
    private $request;

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

        if ($options['download'] == true) {
            $response->headers->set('Content-disposition', "attachment; filename={$file->getName()}");
        }

        if ($options['track'] == true) {
            $event = new FileEvent($file);
            $this->getEventDispatcher()->dispatch('file.render', $event);
        }

        $this->setContent($response, $res);

        return $response;
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
        if (!$this->filelib->getFileOperator()->hasVersion($file, $version)) {
            $response->setStatusCode(404);
            return;
        }

        $provider = $this->filelib->getFileOperator()->getVersionProvider($file, $version);
        $res = $this->getStorage()->retrieveVersion($file, $version);

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

