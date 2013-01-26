<?php

namespace Xi\Filelib\Tests\Tool\UuidGenerator;

use Xi\Filelib\Tool\UuidGenerator\PHPUuidGenerator;

class PHPUuidGeneratorTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Tool\UuidGenerator\PHPUuidGenerator'));
        $this->assertContains('Xi\Filelib\Tool\UuidGenerator\UuidGenerator', class_implements('Xi\Filelib\Tool\UuidGenerator\PHPUuidGenerator'));
    }


    /**
     * @test
     */
    public function v4ShouldGenerateUuidV4()
    {
        $generator = new PHPUuidGenerator();
        $uuid = $generator->v4();
        $this->assertRegExp("/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/", $uuid);
    }

}

