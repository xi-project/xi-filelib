<?php

namespace Xi\Filelib\Tests\Asynchrony\ExecutionStrategy;

use Xi\Filelib\Asynchrony\ExecutionStrategies;
use Xi\Filelib\Asynchrony\ExecutionStrategy\SynchronousExecutionStrategy;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;

require_once __DIR__ . '/touchMyTrallala.php';

class SynchronousExecutionStrategyTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function isNamedCorrectly()
    {
        $strategy = new SynchronousExecutionStrategy();
        $this->assertEquals(ExecutionStrategies::STRATEGY_SYNC, $strategy->getIdentifier());
    }

    /**
     * @test
     */
    public function executes()
    {
        $this->assertFileNotExists(ROOT_TESTS . '/data/temp/ping.txt');
        $strategy = new SynchronousExecutionStrategy();
        $strategy->execute('\touchMyTrallala', [6]);
        $this->assertFileExists(ROOT_TESTS . '/data/temp/ping.txt');
    }

    public function tearDown()
    {
        $deletor = new RecursiveDirectoryDeletor('temp');
        $deletor->delete();
    }
}
