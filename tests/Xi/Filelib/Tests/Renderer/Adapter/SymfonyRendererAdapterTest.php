<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\Renderer\Adapter\SymfonyRendererAdapter;
use Symfony\Component\HttpFoundation\Request;
use Xi\Filelib\Renderer\Response as InternalResponse;

class SymfonyRendererTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function shouldInstantiate()
    {
        $request = Request::create('/lussuti');

        $adapter = new SymfonyRendererAdapter();
        $this->assertNull($adapter->getRequest());
        $adapter->setRequest($request);

        $this->assertSame($request, $adapter->getRequest());

        $adapter2 = new SymfonyRendererAdapter($request);
        $this->assertSame($request, $adapter2->getRequest());
    }

    /**
     * @test
     */
    public function shouldAccelerateWhenHasRequest()
    {
        $request = Request::create('/sussi', 'GET', array(), array(), array(), array('SERVER_SOFTWARE' => 'nginx/1.4.0'));

        $adapter = new SymfonyRendererAdapter();
        $this->assertFalse($adapter->canAccelerate());

        $adapter->setRequest($request);
        $this->assertTrue($adapter->canAccelerate());
    }

    /**
     * @test
     */
    public function getServerSignatureShouldReturnNullWhenNoRequest()
    {
        $adapter = new SymfonyRendererAdapter();
        $this->assertNull($adapter->getServerSignature());
    }


    /**
     * @test
     */
    public function getServerSignatureShouldDelegateToRequest()
    {
        $adapter = new SymfonyRendererAdapter(
            Request::create('/sussi', 'GET', array(), array(), array(), array('SERVER_SOFTWARE' => 'nginx/1.4.0'))
        );

        $this->assertSame('nginx/1.4.0', $adapter->getServerSignature());
    }

    /**
     * @test
     */
    public function returnResponseShouldConvertInternalOKResponseToSymfonyResponse()
    {
        $iResponse = new InternalResponse();
        $iResponse->setContent('tussi');
        $iResponse->setStatusCode(200);
        $iResponse->setHeader('gran', 'oculusso');

        $adapter = new SymfonyRendererAdapter();
        $response = $adapter->returnResponse($iResponse);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertTrue($response->headers->has('gran'));
        $this->assertSame('tussi', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function returnResponseShouldConvertInternalErrorResponseToSymfonyResponse()
    {
        $iResponse = new InternalResponse();
        $iResponse->setContent('tussi');
        $iResponse->setStatusCode(404);
        $iResponse->setHeader('gran', 'oculusso');

        $adapter = new SymfonyRendererAdapter();
        $response = $adapter->returnResponse($iResponse);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertTrue($response->headers->has('gran'));
        $this->assertSame('Not Found', $response->getContent());
        $this->assertSame(404, $response->getStatusCode());
    }

}
