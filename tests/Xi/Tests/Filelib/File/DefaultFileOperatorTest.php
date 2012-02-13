<?php

namespace Xi\Tests\Filelib\File;

use Xi\Tests\Filelib\TestCase as FilelibTestCase;

class DefaultFileOperatorTest extends FilelibTestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\DefaultFileOperator'));
    }
    
    
}