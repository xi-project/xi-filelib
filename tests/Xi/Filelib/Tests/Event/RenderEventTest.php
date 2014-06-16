<?php

namespace Xi\Filelib\Tests\Event;

use Xi\Filelib\Event\RenderEvent;
use Xi\Filelib\Renderer\Response;
use Symfony\Component\HttpFoundation\Response as AdaptedResponse;

class RenderEventTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Event\RenderEvent');
    }

    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $file = $this->getMockedFile();
        $response = new Response();
        $adaptedResponse = new AdaptedResponse();

        $event = new RenderEvent($file, 'gran-tenhunen', $response, $adaptedResponse);

        $this->assertSame($file, $event->getFile());
        $this->assertEquals('gran-tenhunen', $event->getVersion());
        $this->assertSame($response, $event->getInternalResponse());
        $this->assertSame($adaptedResponse, $event->getAdaptedResponse());

        $replacementResponse = new AdaptedResponse();
        $event->setAdaptedResponse($replacementResponse);
        $this->assertSame($replacementResponse, $event->getAdaptedResponse());
    }
}
