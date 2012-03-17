<?php

namespace Xi\Tests\Filelib\Tool\Slugifier;

class AbstractZendSlugifierTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Tool\Slugifier\AbstractZendSlugifier'));
        $this->assertContains('Xi\Filelib\Tool\Slugifier\Slugifier', class_implements('Xi\Filelib\Tool\Slugifier\AbstractZendSlugifier'));
    }
}
