<?php

namespace Xi\Filelib\Tests\Authorization;

use Xi\Filelib\Tests\TestCase;

class AuthorizationPluginTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Authorization\AuthorizationPlugin');
    }
}
