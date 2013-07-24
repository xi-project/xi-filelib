<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Renderer\Adapter;

use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Xi\Filelib\Renderer\AcceleratedRendererAdapter;
use Xi\Filelib\Renderer\RendererAdapter;

use Xi\Filelib\Renderer\Response as InternalResponse;

class SymfonyRendererAdapter implements RendererAdapter
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
     * @param Response   $response
     * @param FileObject $res
     */
    private function accelerateResponse(Response $response, FileObject $res)
    {
        $path = preg_replace("[^{$this->getStripPrefixFromAcceleratedPath()}]", '', $res->getRealPath());
        $path = $this->getAddPrefixToAcceleratedPath() . $path;

        $response->headers->set($this->getAccelerationHeader(), $path);
    }



    public function returnResponse(InternalResponse $iResponse)
    {
        $response = new Response(
            '',
            $iResponse->getContent(),
            $iResponse->getStatusCode()
        );

        foreach ($iResponse->getHeaders() as $key => $value) {
            $response->headers->set($key, $value);
        }

        if ($response->getStatusCode() !== 200) {
            $response->setContent(Response::$statusTexts[$response->getStatusCode()]);
            return $response;
        }

        $response->headers->set('tussi', 'magic marker');

        return $response;
    }

}
