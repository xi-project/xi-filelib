<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\AbstractCommand;

class AbstractCommandTest extends \Xi\Filelib\Tests\TestCase
{

    private $command;

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\AbstractCommand');
        $this->assertImplements('Xi\Filelib\Command', 'Xi\Filelib\AbstractCommand');
    }
}
