<?php

namespace Xi\Filelib\Tests\Asynchrony\ExecutionStrategy;

use Pekkis\Queue\Adapter\PhpAMQPAdapter;
use Pekkis\Queue\Processor\Processor;
use Xi\Filelib\Asynchrony\ExecutionStrategy\PekkisQueueExecutionStrategy;
use Xi\Filelib\Asynchrony\Queue\FilelibMessageHandler;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;
use Xi\Filelib\Tests\Storage\Adapter\MemoryStorageAdapter;

require_once __DIR__ . '/touchMyTrallala.php';

class PekkisQueueExecutionStrategyTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var PhpAMQPAdapter
     */
    private $adapter;

    public function setUp()
    {
        $this->adapter = new PhpAMQPAdapter(
            RABBITMQ_HOST,
            RABBITMQ_PORT,
            RABBITMQ_USERNAME,
            RABBITMQ_PASSWORD,
            RABBITMQ_VHOST,
            'filelib_asynchrony_exchange',
            'filelib_asynchrony_queue'
        );
    }

    public function tearDown()
    {
        $deletor = new RecursiveDirectoryDeletor('temp');
        $deletor->delete();

        $this->adapter->purge();
    }


    /**
     * @test
     */
    public function executes()
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter()
        );

        $this->assertFileNotExists(ROOT_TESTS . '/data/temp/ping.txt');

        $strategy = new PekkisQueueExecutionStrategy(
            $this->adapter,
            $filelib
        );

        $strategy->execute('\touchMyTrallala', [6]);

        $this->assertFileNotExists(ROOT_TESTS . '/data/temp/ping.txt');

        $processor = new Processor(
            $strategy->getQueue()
        );
        $processor->registerHandler(new FilelibMessageHandler());
        $result = $processor->process();

        $this->assertTrue($result);
        $this->assertFileExists(ROOT_TESTS . '/data/temp/ping.txt');
    }
}
