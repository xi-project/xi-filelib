<?php

namespace Xi\Filelib\Tests\Authorization;

use Xi\Filelib\Tests\TestCase;

class AuthorizationPluginTest extends TestCase
{
    public function setUp()
    {
        $this->adapter = $this->getMock('Xi\Filelib\Authorization\AuthorizationAdapter');
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Authorization\AuthorizationPlugin');
    }


}
