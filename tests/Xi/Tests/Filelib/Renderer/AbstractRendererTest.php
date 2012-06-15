<?php

namespace Xi\Tests\Filelib\Renderer;

use Xi\Filelib\Renderer\AbstractRenderer;
use Xi\Filelib\File\File;

class AbstractRendererTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function mergeOptionsShouldReturnSanitizedResult()
    {

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractRenderer')
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $expected = array(
            'version' => 'original',
            'download' => false,
            'track' => false,
        );

        $options = array();

        $this->assertEquals($expected, $renderer->mergeOptions($options));

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

        $this->assertEquals($expected, $renderer->mergeOptions($options));

    }


    /**
     * @test
     */
    public function getPublisherShouldDelegateToFilelib()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractRenderer')
            ->setConstructorArgs(array($filelib))
            ->getMockForAbstractClass();

        $filelib->expects($this->once())->method('getPublisher');

        $publisher = $renderer->getPublisher();
    }




    /**
     * @test
     */
    public function getAclShouldDelegateToFilelib()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractRenderer')
            ->setConstructorArgs(array($filelib))
            ->getMockForAbstractClass();


        $filelib->expects($this->once())->method('getAcl');

        $acl = $renderer->getAcl();
    }


    /**
     * @test
     */
    public function getStorageShouldDelegateToFilelib()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractRenderer')
            ->setConstructorArgs(array($filelib))
            ->getMockForAbstractClass();

        $filelib->expects($this->once())->method('getStorage');

        $acl = $renderer->getStorage();
    }

    /**
     * @test
     */
    public function getUrlShouldDelegateToPublisherWhenUsingOriginalVersion()
    {
        $file = File::create(array('id' => 1));

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractRenderer')
            ->setConstructorArgs(array($filelib))
            ->setMethods(array('getPublisher'))
            ->getMock();

        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $publisher->expects($this->once())->method('getUrl')->with($this->equalTo($file));

        $renderer->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));

        $url = $renderer->getUrl($file, array('version' => 'original'));



    }


    /**
     * @test
     */
    public function getUrlShouldDelegateToPublisherWhenUsingNonOriginalVersion()
    {
        $file = File::create(array('id' => 1));

        $vp = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));

        $fiop->expects($this->once())->method('getVersionProvider')
            ->with($this->equalTo($file), $this->equalTo('lussen'))
            ->will($this->returnValue($vp));

        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\AbstractRenderer')
            ->setConstructorArgs(array($filelib))
            ->setMethods(array('getPublisher'))
            ->getMock();

        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $publisher->expects($this->once())->method('getUrlVersion')->with($this->equalTo($file), $this->equalTo('lussen'), $this->equalTo($vp));

        $renderer->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));

        $url = $renderer->getUrl($file, array('version' => 'lussen'));

    }


}

