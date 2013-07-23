<?php

namespace Xi\Filelib\Tests\Acl;

use Xi\Filelib\Tests\TestCase;

class AclPluginTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Acl\AclPlugin');
    }
}
