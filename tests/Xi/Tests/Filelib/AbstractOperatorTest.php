<?php

namespace Xi\Tests\Filelib;

class AbstractOperatorTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\AbstractOperator'));
    }
    
    
}