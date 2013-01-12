<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Renderer;

use Xi\Filelib\File\File;
use Zend_Controller_Request_Http as Request;
use Zend_Controller_Response_Http as Response;
use Xi\Filelib\File\FileObject;

class ZendRenderer extends AbstractAcceleratedRenderer implements AcceleratedRenderer
{
    /**
     * @var array Status texts for response types (courtesy of Symfony 2)
     */
    static public $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
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

        $serverSignature = $request->getServer('SERVER_SOFTWARE');

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
        $response->headersSentThrowsException = false;

        if (!$this->getAcl()->isFileReadable($file)) {
            $response->setHttpResponseCode(403);
            $response->setBody(self::$statusTexts[$response->getHttpResponseCode()]);
            return $response;
        }

        if ($options['version'] === 'original') {
            $res = $this->respondToOriginal($file, $response);
        } else {
            $res = $this->respondToVersion($file, $response, $options['version']);
        }

        // If not 200 swiftly exit here
        if ($response->getHttpResponseCode() !== 200) {
            $response->setBody(self::$statusTexts[$response->getHttpResponseCode()]);
            return $response;
        }

        if ($options['download'] == true) {
            $response->setHeader('Content-disposition', "attachment; filename={$file->getName()}");
        }

        if ($options['track'] == true) {
            $this->dispatchTrackEvent($file);
        }

        $this->setBody($response, $res);

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
            $response->setHttpResponseCode(403);
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
            $response->setHttpResponseCode(404);
            return;
        }

        $res = $this->getStorage()->retrieveVersion($file->getResource(), $version);
        return $res;
    }

    /**
     * Sets content to response
     */
    private function setBody(Response $response, FileObject $res)
    {
        $response->setHeader('Content-Type', $res->getMimetype());

        if ($this->isAccelerationEnabled() && $this->isAccelerationPossible()) {
            $this->accelerateResponse($response, $res);
            return;
        }

        $content = file_get_contents($res->getPathname());
        $response->setBody($content);
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
        $response->setHeader($this->getAccelerationHeader(), $path);
    }

}
