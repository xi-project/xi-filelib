<?php

namespace Xi\Filelib\Tests\Asynchrony\Queue;

use Pekkis\Queue\Message;
use Xi\Filelib\Asynchrony\Queue\FilelibMessageHandler;
use Xi\Filelib\Asynchrony\Serializer\SerializedCallback;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;
use Xi\Filelib\Tests\TestCase;

class FilelibMessageHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function handles()
    {
        $data = new SerializedCallback('\touchMyTrallala', ['xooxer']);
        $message = Message::create('xoo.lus', $data);

        $this->assertFileNotExists(ROOT_TESTS . '/data/temp/ping.txt');

        $handler = new FilelibMessageHandler();

        $handler->handle($message, $this->prophesize('Pekkis\Queue\Queue')->reveal());

        $this->assertFileExists(ROOT_TESTS . '/data/temp/ping.txt');
    }

    public function tearDown()
    {
        $deletor = new RecursiveDirectoryDeletor('temp');
        $deletor->delete();
    }
}
