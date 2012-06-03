<?php

namespace Xi\Tests\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use Xi\Tests\Filelib\TestCase;

/**
 * @group storage
 */
class DirectoryIdCalculatorTest extends TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator'));
    }
}
