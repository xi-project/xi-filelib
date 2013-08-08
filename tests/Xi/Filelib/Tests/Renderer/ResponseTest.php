<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\Renderer\Response;

class ResponseTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Renderer\Response');
    }

    /**
     * @test
     */
    public function getHeaderShouldReturnHeaderOrDefault()
    {
        $response = new Response();
        $this->assertNull($response->getHeader('tussi'));
        $this->assertSame('lussi', $response->getHeader('tussi', 'lussi'));

        $response->setHeader('tussi', 'watussi');
        $this->assertSame('watussi', $response->getHeader('tussi', 'lussi'));
    }


}
