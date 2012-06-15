<?php

namespace Xi\Tests\Filelib\Renderer;

use Xi\Filelib\Renderer\AbstractAcceleratedRenderer;
use Xi\Filelib\File\File;

class AbstractAcceleratedRendererTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function accelerationShouldBeDisabledByDefault()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractAcceleratedRenderer')
            ->setConstructorArgs(array($filelib))
            ->getMockForAbstractClass();

        $this->assertFalse($renderer->isAccelerationEnabled());
    }

    /**
     * @test
     */
    public function enableAccelerationShouldEnableAcceleration()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractAcceleratedRenderer')
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $this->assertFalse($renderer->isAccelerationEnabled());

        $renderer->enableAcceleration(true);

        $this->assertTrue($renderer->isAccelerationEnabled());
    }


    /**
     * @test
     */
    public function stripPrefixFromAcceleratedPathShouldDefaultToEmptyString()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractAcceleratedRenderer')
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $this->assertEquals('', $renderer->getStripPrefixFromAcceleratedPath());

    }

    /**
     * @test
     */
    public function stripPrefixFromAcceleratedPathShouldObeySetter()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractAcceleratedRenderer')
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $this->assertEquals('', $renderer->getStripPrefixFromAcceleratedPath());

        $renderer->setStripPrefixFromAcceleratedPath('luss');

        $this->assertEquals('luss', $renderer->getStripPrefixFromAcceleratedPath());
    }


    /**
     * @test
     */
    public function addPrefixToAcceleratedPathShouldDefaultToEmptyString()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractAcceleratedRenderer')
                        ->setConstructorArgs(array($filelib))
                        ->getMockForAbstractClass();

        $this->assertEquals('', $renderer->getAddPrefixToAcceleratedPath());

    }


    /**
     * @test
     */
    public function addPrefixToAcceleratedPathShouldObeySetter()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractAcceleratedRenderer')
                          ->setConstructorArgs(array($filelib))
                          ->getMockForAbstractClass();

        $this->assertSame('', $renderer->getAddPrefixToAcceleratedPath());

        $renderer->setAddPrefixToAcceleratedPath('luss');

        $this->assertSame('luss', $renderer->getAddPrefixToAcceleratedPath());
    }





}
