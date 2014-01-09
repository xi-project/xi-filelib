<?php

namespace Xi\Filelib\Tests\Tool;

use Xi\Filelib\Tool\ExtensionRequirements;

class ExtensionRequirementsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function throwsExceptionWhenExtensionIsNotLoaded()
    {
        $this->setExpectedException('RuntimeException');
        ExtensionRequirements::requireVersion('lussogrande', '1.0.0');

    }

    /**
     * @test
     */
    public function throwsExceptionWhenExtensionIsTooOld()
    {
        $this->setExpectedException('RuntimeException', "Requires extension 'spl', version 2.0.0");
        ExtensionRequirements::requireVersion('spl', '2.0.0');
    }

    /**
     * @test
     */
    public function doesntThrowUpWhenAllIsWell()
    {
        ExtensionRequirements::requireVersion('SPL');
        ExtensionRequirements::requireVersion('SPL', '0.1');
    }



}
