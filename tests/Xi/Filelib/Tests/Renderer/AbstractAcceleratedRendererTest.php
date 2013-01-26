<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\Renderer\AbstractAcceleratedRenderer;

class AbstractAcceleratedRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractAcceleratedRenderer
     */
    protected $renderer;

    public function setUp()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $this->renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractAcceleratedRenderer')
            ->setConstructorArgs(array($filelib))
            ->getMockForAbstractClass();
    }

    /**
     * @test
     */
    public function accelerationShouldBeDisabledByDefault()
    {

        $this->assertFalse($this->renderer->isAccelerationEnabled());
    }

    /**
     * @test
     */
    public function enableAccelerationShouldEnableAcceleration()
    {
        $this->assertFalse($this->renderer->isAccelerationEnabled());
        $this->renderer->enableAcceleration(true);
        $this->assertTrue($this->renderer->isAccelerationEnabled());
    }

    /**
     * @test
     */
    public function stripPrefixFromAcceleratedPathShouldDefaultToEmptyString()
    {
        $this->assertEquals('', $this->renderer->getStripPrefixFromAcceleratedPath());

    }

    /**
     * @test
     */
    public function stripPrefixFromAcceleratedPathShouldObeySetter()
    {
        $this->assertEquals('', $this->renderer->getStripPrefixFromAcceleratedPath());

        $this->renderer->setStripPrefixFromAcceleratedPath('luss');

        $this->assertEquals('luss', $this->renderer->getStripPrefixFromAcceleratedPath());
    }

    /**
     * @test
     */
    public function addPrefixToAcceleratedPathShouldDefaultToEmptyString()
    {
        $this->assertEquals('', $this->renderer->getAddPrefixToAcceleratedPath());
    }

    /**
     * @test
     */
    public function addPrefixToAcceleratedPathShouldObeySetter()
    {
        $this->assertSame('', $this->renderer->getAddPrefixToAcceleratedPath());

        $this->renderer->setAddPrefixToAcceleratedPath('luss');

        $this->assertSame('luss', $this->renderer->getAddPrefixToAcceleratedPath());
    }

}
