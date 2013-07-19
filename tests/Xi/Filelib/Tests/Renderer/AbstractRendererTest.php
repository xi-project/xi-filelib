<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\File\File;

class AbstractRendererTest extends \Xi\Filelib\Tests\TestCase
{

    protected $renderer;

    protected $configuration;

    protected $publisher;

    protected $fiop;

    protected $storage;

    protected $ed;

    public function setUp()
    {
        $this->fiop = $this->getMockedFileOperator();
        $this->ed = $this->getMockedEventDispatcher();
        $this->storage = $this->getMockedStorage();

        $filelib = $this->getMockedFilelib(null, $this->fiop);
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($this->ed));

        $this->renderer = $this
            ->getMockBuilder('Xi\Filelib\Renderer\AbstractRenderer')
            ->setConstructorArgs(
                array(
                    $filelib,
                )
            )
            ->setMethods(array('render'))
            ->getMock();
    }

    /**
     * @test
     * @todo This should be a protected method (refuctor away later)
     */
    public function mergeOptionsShouldReturnSanitizedResult()
    {

        $expected = array(
            'version' => 'original',
            'download' => false,
        );

        $options = array();

        $this->assertEquals($expected, $this->renderer->mergeOptions($options));

        $expected = array(
            'version' => 'orignaluss',
            'download' => false,
            'impossible' => 'impossibru',
        );

        $options = array(
            'version' => 'orignaluss',
            'impossible' => 'impossibru',
        );

        $this->assertEquals($expected, $this->renderer->mergeOptions($options));

    }
}
