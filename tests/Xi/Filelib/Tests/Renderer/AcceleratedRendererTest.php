<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\Renderer\AcceleratedRenderer;
use Xi\Filelib\File\File;
use Xi\Filelib\Renderer\Events;

class AcceleratedRendererTest extends RendererTest
{
    /**
     * @var AcceleratedRenderer
     */
    protected $renderer;

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
        $this->renderer->enableAcceleration(true);
        $this->assertTrue($this->renderer->isAccelerationEnabled());
    }

    /**
     * @return
     */
    public function  shouldSetupAccelerationCorrectly()
    {
        $this->renderer->enableAcceleration(true);
    }

    /**
     * @test
     * @dataProvider provideOptions
     */
    public function shouldDefaultToUnacceleratedFunctionalityWhenCantAccelerate($download, $sharedVersions)
    {
        $resource = $this->getMockedResource();
        $file = File::create(array('resource' => $resource, 'name' => 'lussuti.pdf'));

        $this->ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::RENDERER_BEFORE_RENDER, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $this->pm
            ->expects($this->once())
            ->method('hasVersion')
            ->with($file, 'xooxer')
            ->will($this->returnValue(true));

        $this->storage
            ->expects($this->once())
            ->method('retrieveVersion')
            ->with($resource, 'xooxer', ($sharedVersions) ? null : $file)
            ->will($this->returnValue(ROOT_TESTS . '/data/refcard.pdf'));

        $vp = $this->getMockedVersionProvider('xooxer');
        $vp->expects($this->any())->method('areSharedVersionsAllowed')->will($this->returnValue($sharedVersions));

        $this->pm
            ->expects($this->once())
            ->method('getVersionProvider')
            ->with($file, 'xooxer')
            ->will($this->returnValue($vp));

        $ret = $this->renderer->render($file, 'xooxer', array('download' => $download));

        $this->assertInstanceOf('Xi\Filelib\Renderer\Response', $ret);
        $this->assertNotSame('', $ret->getContent());
        $this->assertSame(200, $ret->getStatusCode());

        $expectedHeaders = array(
            'Content-Type' => 'application/pdf',
        );
        if ($download) {
            $expectedHeaders['Content-disposition'] = "attachment; filename={$file->getName()}";
        }

        $this->assertEquals($expectedHeaders, $ret->getHeaders());

    }


    public function provideAcceleratedOptions()
    {
       return array(
           array(false, false, true, 'KobroServer 1.0.0', false),
           array(true, true, false, 'nginx/1.3.9', false),
           array(true, true, true, 'lusso/1.3.9', false),
           array(true, true, true, 'nginx/1.3.9', true, 'x-accel-redirect', '', '',  ROOT_TESTS . '/data'),
           array(true, true, true, 'nginx/1.3.9', true, 'x-accel-redirect',  ROOT_TESTS . '/data', '/lusso', '/lusso'),
           array(true, true, true, 'Apache', true, 'x-sendfile', ROOT_TESTS . '/data', '/tussi', '/tussi'),
       );
    }


    /**
     * @test
     * @dataProvider provideAcceleratedOptions
     */
    public function shouldSetupAcceleratedResponseCorrectly
    (
        $download,
        $enableAcceleration,
        $canAccelerate,
        $serverSignature,
        $expectAccel,
        $expectedHeader = '',
        $stripPrefix = '',
        $addPrefix = '',
        $expectedPath = null
    ) {

        $this->renderer->enableAcceleration($enableAcceleration);
        $this->renderer->stripPrefixFromPath($stripPrefix);
        $this->renderer->addPrefixToPath($addPrefix);

        $resource = $this->getMockedResource();
        $file = File::create(array('resource' => $resource, 'name' => 'lussuti.pdf'));

        $this->adapter
            ->expects($this->any())
            ->method('canAccelerate')
            ->will($this->returnValue($canAccelerate));

        $this->adapter
            ->expects($this->any())
            ->method('getServerSignature')
            ->will($this->returnValue($serverSignature));

        $this->ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::RENDERER_BEFORE_RENDER, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $this->pm
            ->expects($this->once())
            ->method('hasVersion')
            ->with($file, 'xooxer')
            ->will($this->returnValue(true));

        $this->storage
            ->expects($this->once())
            ->method('retrieveVersion')
            ->with($resource, 'xooxer', null)
            ->will($this->returnValue(ROOT_TESTS . '/data/refcard.pdf'));

        $vp = $this->getMockedVersionProvider('xooxer');
        $vp->expects($this->any())->method('areSharedVersionsAllowed')->will($this->returnValue(true));

        $this->pm
            ->expects($this->once())
            ->method('getVersionProvider')
            ->with($file, 'xooxer')
            ->will($this->returnValue($vp));

        $ret = $this->renderer->render($file, 'xooxer', array('download' => $download));

        $this->assertInstanceOf('Xi\Filelib\Renderer\Response', $ret);

        $this->assertSame(200, $ret->getStatusCode());

        $expectedHeaders = array(
            'Content-Type' => 'application/pdf',
        );
        if ($download) {
            $expectedHeaders['Content-disposition'] = "attachment; filename={$file->getName()}";
        }



        if (!$expectAccel) {
            $this->assertNotSame('', $ret->getContent());
        } else {
            $this->assertSame('', $ret->getContent());
            $expectedHeaders[$expectedHeader] = $expectedPath . '/refcard.pdf';
        }

        $this->assertEquals($expectedHeaders, $ret->getHeaders());
    }



    protected function getAdapter()
    {
        return $this->getMock('Xi\Filelib\Renderer\AcceleratedRendererAdapter');
    }

    protected function getRenderer($adapter)
    {
        $renderer = new AcceleratedRenderer(
            $this->filelib,
            $adapter
        );

        return $renderer;
    }


}
