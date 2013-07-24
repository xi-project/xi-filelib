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

use Xi\Filelib\Renderer\Response as InternalResponse;

class SymfonyRendererAdapter implements AcceleratedRendererAdapter
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request = null)
    {
        $this->request = $request;
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

    public function canAccelerate()
    {
        // If we have no request as context we cannot accelerate
        if (!$this->getRequest()) {
            return false;
        }
        return true;
    }

    public function returnResponse(InternalResponse $iResponse)
    {
        $response = new Response(
            $iResponse->getContent(),
            $iResponse->getStatusCode(),
            $iResponse->getHeaders()
        );

        if ($response->getStatusCode() !== 200) {
            $response->setContent(Response::$statusTexts[$response->getStatusCode()]);
            return $response;
        }
        return $response;
    }

    public function getServerSignature()
    {
        if (!$this->getRequest()) {
            return null;
        }
        return $this->getRequest()->server->get('SERVER_SOFTWARE');
    }
}
