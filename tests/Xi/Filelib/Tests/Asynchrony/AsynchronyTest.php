<?php

namespace Xi\Filelib\Tests\Asynchrony;

use Xi\Filelib\Asynchrony\Asynchrony;
use Xi\Filelib\Asynchrony\ExecutionStrategies;
use Xi\Filelib\Asynchrony\ExecutionStrategy\SynchronousExecutionStrategy;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\Storage\Adapter\MemoryStorageAdapter;
use Xi\Filelib\Tests\TestCase;

class AsynchronyTest extends TestCase
{
    /**
     * @test
     */
    public function initializes()
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter()
        );

        $asynchrony = new Asynchrony($filelib);

        $this->assertInstanceOf(
            'Xi\Filelib\Asynchrony\FileRepository',
            $filelib->getFileRepository()
        );

        $this->assertInstanceOf(
            'Xi\Filelib\Asynchrony\ExecutionStrategy\SynchronousExecutionStrategy',
            $asynchrony->getStrategy(ExecutionStrategies::STRATEGY_SYNC)
        );
    }

    /**
     * @test
     */
    public function throwsUpAtGettingNonexistantStrategy()
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter()
        );
        $asynchrony = new Asynchrony($filelib);
        $this->setExpectedException('Xi\Filelib\LogicException');

        $asynchrony->getStrategy(ExecutionStrategies::STRATEGY_ASYNC_PEKKIS_QUEUE);
    }

    /**
     * @test
     */
    public function cantAddSameStrategyTwice()
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter()
        );
        $asynchrony = new Asynchrony($filelib);

        $this->setExpectedException('Xi\Filelib\LogicException');
        $asynchrony->addStrategy(new SynchronousExecutionStrategy());
    }

    /**
     * @test
     */
    public function canAddStrategy()
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter()
        );
        $asynchrony = new Asynchrony($filelib);

        $strategy = $this->prophesize('Xi\Filelib\Asynchrony\ExecutionStrategy\ExecutionStrategy');
        $strategy->getIdentifier()->willReturn('xooxoxx');
        $strategy->attachTo($filelib)->shouldBeCalled();

        $asynchrony->addStrategy($strategy->reveal());
    }

}