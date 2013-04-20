<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\Renderer\AbstractAcceleratedRenderer;

class AbstractAcceleratedRendererTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var AbstractAcceleratedRenderer
     */
    protected $renderer;

    protected $configuration;

    protected $publisher;

    protected $fiop;

    protected $acl;

    protected $storage;

    protected $ed;

    public function setUp()
    {
        $this->fiop = $this->getMockedFileOperator();
        $this->publisher = $this->getMockedPublisher();
        $this->acl = $this->getMockedAcl();
        $this->ed = $this->getMockedEventDispatcher();
        $this->storage = $this->getMockedStorage();

        $this->renderer = $this
            ->getMockBuilder('Xi\Filelib\Renderer\AbstractAcceleratedRenderer')
            ->setConstructorArgs(
                array(
                    $this->storage,
                    $this->publisher,
                    $this->acl,
                    $this->ed,
                    $this->fiop,
                )
            )
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
