<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\File\File;

class AbstractRendererTest extends \Xi\Filelib\Tests\TestCase
{

    protected $renderer;

    protected $filelib;

    protected $publisher;

    protected $fiop;

    public function setUp()
    {
        $this->filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $this->fiop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();
        $this->filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($this->fiop));

        $this->renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractRenderer')
                               ->setConstructorArgs(array($this->filelib))
                               ->setMethods(array('getPublisher'))
                               ->getMock();

        $this->publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $this->renderer->expects($this->any())->method('getPublisher')->will($this->returnValue($this->publisher));
    }

    /**
     * @test
     */
    public function mergeOptionsShouldReturnSanitizedResult()
    {

        $expected = array(
            'version' => 'original',
            'download' => false,
            'track' => false,
        );

        $options = array();

        $this->assertEquals($expected, $this->renderer->mergeOptions($options));

        $expected = array(
            'version' => 'orignaluss',
            'download' => false,
            'impossible' => 'impossibru',
            'track' => true,
        );

        $options = array(
            'version' => 'orignaluss',
            'impossible' => 'impossibru',
            'track' => true,
        );

        $this->assertEquals($expected, $this->renderer->mergeOptions($options));

    }

    /**
     * @test
     */
    public function getPublisherShouldDelegateToFilelib()
    {
        $this->filelib->expects($this->once())->method('getPublisher');

        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractRenderer')
            ->setConstructorArgs(array($this->filelib))
            ->getMockForAbstractClass();

        $publisher = $renderer->getPublisher();
    }

    /**
     * @test
     */
    public function getAclShouldDelegateToFilelib()
    {

        $this->filelib->expects($this->once())->method('getAcl');

        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractRenderer')
            ->setConstructorArgs(array($this->filelib))
            ->getMockForAbstractClass();

        $acl = $renderer->getAcl();
    }

    /**
     * @test
     */
    public function getStorageShouldDelegateToFilelib()
    {
        $this->filelib->expects($this->once())->method('getStorage');

        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractRenderer')
            ->setConstructorArgs(array($this->filelib))
            ->getMockForAbstractClass();

        $acl = $renderer->getStorage();
    }

    /**
     * @test
     */
    public function getUrlShouldDelegateToPublisherWhenUsingOriginalVersion()
    {
        $file = File::create(array('id' => 1));
        $this->publisher->expects($this->once())->method('getUrl')->with($this->equalTo($file));
        $this->renderer->getUrl($file, array('version' => 'original'));
    }

    /**
     * @test
     */
    public function getUrlShouldDelegateToPublisherWhenUsingNonOriginalVersion()
    {
        $file = File::create(array('id' => 1));

        $vp = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');

        $this->fiop->expects($this->once())->method('getVersionProvider')
            ->with($this->equalTo($file), $this->equalTo('lussen'))
            ->will($this->returnValue($vp));

        $this->publisher->expects($this->once())->method('getUrlVersion')->with($this->equalTo($file), $this->equalTo('lussen'), $this->equalTo($vp));

        $this->renderer->getUrl($file, array('version' => 'lussen'));

    }

}
