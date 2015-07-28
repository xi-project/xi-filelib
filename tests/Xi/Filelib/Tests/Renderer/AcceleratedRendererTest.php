<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\Renderer\AcceleratedRenderer;
use Xi\Filelib\File\File;
use Xi\Filelib\Renderer\Events;
use Xi\Filelib\Version;
use Xi\Filelib\Resource\Resource;

class AcceleratedRendererTest extends RendererTestCase
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
     * @test
     * @dataProvider provideOptions
     */
    public function shouldDefaultToUnacceleratedFunctionalityWhenCantAccelerate($download, $sharedVersions)
    {
        $resource = Resource::create()->addVersion(Version::get('xooxer'));
        $file = File::create(
            array(
                'resource' => $resource, 'name' => 'lussuti.pdf'
            )
        )->addVersion(Version::get('xooxer'));

        $this->ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::RENDERER_BEFORE_RENDER, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $this->storage
            ->expects($this->once())
            ->method('retrieveVersion')
            ->with($sharedVersions ? $resource : $file, Version::get('xooxer'))
            ->will($this->returnValue(ROOT_TESTS . '/data/refcard.pdf'));

        $vp = $this->getMockedVersionProvider();
        $vp
            ->expects($this->any())
            ->method('getApplicableVersionable')
            ->will($this->returnValue($sharedVersions ? $resource : $file));

        $vp
            ->expects($this->any())
            ->method('ensureValidVersion')
            ->with($this->isInstanceOf('Xi\Filelib\Version'))
            ->will($this->returnArgument(0));


        $this->pm
            ->expects($this->any())
            ->method('getVersionProvider')
            ->with($file, Version::get('xooxer'))
            ->will($this->returnValue($vp));

        $ret = $this->renderer->render($file, Version::get('xooxer'), array('download' => $download));

        $this->assertInstanceOf('Xi\Filelib\Renderer\Response', $ret);

        $this->assertEquals(200, $ret->getStatusCode());
        $this->assertNotEquals('', $ret->getContent());


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

        $resource = Resource::create()->addVersion(Version::get('xooxer'));
        $file = File::create(
            array(
                'resource' => $resource, 'name' => 'lussuti.pdf'
            )
        )->addVersion(Version::get('xooxer'));

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

        $this->storage
            ->expects($this->once())
            ->method('retrieveVersion')
            ->with($resource, Version::get('xooxer'))
            ->will($this->returnValue(ROOT_TESTS . '/data/refcard.pdf'));

        $vp = $this->getMockedVersionProvider();
        $vp
            ->expects($this->any())
            ->method('getApplicableVersionable')
            ->will($this->returnValue($resource));

        $vp->expects($this->any())
            ->method('ensureValidVersion')
            ->with($this->equalTo(Version::get('xooxer')))
            ->will($this->returnArgument(0));

        $this->pm
            ->expects($this->any())
            ->method('getVersionProvider')
            ->with($file, Version::get('xooxer'))
            ->will($this->returnValue($vp));

        $ret = $this->renderer->render($file, Version::get('xooxer'), array('download' => $download));

        $this->assertInstanceOf('Xi\Filelib\Renderer\Response', $ret);
        $this->assertEquals(200, $ret->getStatusCode());

        $expectedHeaders = array(
            'Content-Type' => 'application/pdf',
        );
        if ($download) {
            $expectedHeaders['Content-disposition'] = "attachment; filename={$file->getName()}";
        }

        if (!$expectAccel) {
            $this->assertNotEquals('', $ret->getContent());
        } else {
            $this->assertEquals('', $ret->getContent());
            $expectedHeaders[$expectedHeader] = $expectedPath . '/refcard.pdf';
        }

        $this->assertEquals($expectedHeaders, $ret->getHeaders());
    }

    public function getAdapter()
    {
        return $this->getMock('Xi\Filelib\Renderer\Adapter\AcceleratedRendererAdapter');
    }

    public function getRenderer($adapter)
    {
        $renderer = new AcceleratedRenderer(
            $adapter
        );
        $renderer->attachTo($this->filelib);

        return $renderer;
    }


}
