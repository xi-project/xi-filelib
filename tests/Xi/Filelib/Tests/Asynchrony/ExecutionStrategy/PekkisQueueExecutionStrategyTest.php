<?php

namespace Xi\Filelib\Tests\Asynchrony\ExecutionStrategy;

use Pekkis\Queue\Adapter\PhpAMQPAdapter;
use Pekkis\Queue\Processor\Processor;
use Pekkis\Queue\Queue;
use Pekkis\Queue\SymfonyBridge\EventDispatchingQueue;
use Xi\Filelib\Asynchrony\ExecutionStrategies;
use Xi\Filelib\Asynchrony\ExecutionStrategy\PekkisQueueExecutionStrategy;
use Xi\Filelib\Asynchrony\Queue\FilelibMessageHandler;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;
use Xi\Filelib\Tests\Storage\Adapter\MemoryStorageAdapter;

use Pekkis\Queue\Adapter\PeclAMQPAdapter;

require_once __DIR__ . '/touchMyTrallala.php';

class PekkisQueueExecutionStrategyTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var Queue
     */
    private $queue;

    public function setUp()
    {
        if (!getenv("RABBITMQ_HOST")) {
            return $this->markTestSkipped('RabbitMQ not configured');
        }

        $this->queue = new Queue(
            new PeclAMQPAdapter(
                getenv("RABBITMQ_HOST"),
                getenv("RABBITMQ_PORT"),
                getenv("RABBITMQ_USER"),
                getenv("RABBITMQ_PASSWORD"),
                getenv("RABBITMQ_VHOST"),
                "filelib_e",
                "filelib_q"
            )
        );
        $this->queue->purge();
    }

    public function tearDown()
    {
        $deletor = new RecursiveDirectoryDeletor('temp');
        $deletor->delete();
    }

    /**
     * @test
     */
    public function isNamedCorrectly()
    {
        $strategy = new PekkisQueueExecutionStrategy(
            $this->queue
        );
        $this->assertEquals(ExecutionStrategies::STRATEGY_ASYNC_PEKKIS_QUEUE, $strategy->getIdentifier());
    }

    /**
     * @test
     */
    public function failsToExecuteWhenNotAttached()
    {
        $this->assertFileNotExists(ROOT_TESTS . '/data/temp/ping.txt');

        $strategy = new PekkisQueueExecutionStrategy(
            $this->queue
        );

        $this->setExpectedException('Xi\Filelib\LogicException');
        $strategy->execute('\touchMyTrallala', [6]);
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
            $this->queue
        );
        $strategy->attachTo($filelib);

        $strategy->execute('\touchMyTrallala', [6]);

        $this->assertFileNotExists(ROOT_TESTS . '/data/temp/ping.txt');

        $processor = new Processor(
            new EventDispatchingQueue($strategy->getQueue(), $filelib->getEventDispatcher())
        );
        $processor->registerHandler(new FilelibMessageHandler());
        $result = $processor->process();

        $this->assertTrue($result);
        $this->assertFileExists(ROOT_TESTS . '/data/temp/ping.txt');
    }
}
