<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\Version;
use Xi\Filelib\Renderer\Renderer;
use Xi\Filelib\Renderer\Events;
use Xi\Filelib\Authorization\AccessDeniedException;
use Xi\Filelib\File\File;
use Xi\Filelib\Resource\Resource;


abstract class RendererTestCase extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fiop;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filelib;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ed;

    abstract public function getAdapter();

    abstract public function getRenderer($adapter);

    public function setUp()
    {
        $this->ed = $this->getMockedEventDispatcher();
        $this->fiop = $this->getMockedFileRepository();
        $this->storage = $this->getMockedStorage();
        $this->pm = $this->getMockedProfileManager();
        $this->filelib = $this->getMockedFilelib(
            null,
            $this->fiop,
            null,
            $this->storage,
            $this->ed,
            null,
            null,
            null,
            $this->pm
        );

        $this->adapter = $this->getAdapter();
        $this->adapter
            ->expects($this->any())
            ->method('adaptResponse')
            ->with($this->isInstanceOf('Xi\Filelib\Renderer\Response'))
            ->will($this->returnArgument(0));

        $this->renderer = $this->getRenderer($this->adapter);
    }


    /**
     * @test
     */
    public function shouldTryToFindWhileWhenRenderIsCalledWithId()
    {
        $this->fiop
            ->expects($this->once())
            ->method('find')
            ->with('xooxoo')
            ->will($this->returnValue(false));

        $ret = $this->renderer->render('xooxoo', 'xooxer');

        $this->assertInstanceOf('Xi\Filelib\Renderer\Response', $ret);
        $this->assertSame('', $ret->getContent());
        $this->assertSame(404, $ret->getStatusCode());
        $this->assertEquals(array(), $ret->getHeaders());
    }

    /**
     * @test
     */
    public function authorizationErrorShouldLeadTo403()
    {
        $file = $this->getMockedFile();
        $this->fiop
            ->expects($this->once())
            ->method('find')
            ->with('xooxoo')
            ->will($this->returnValue($file));

        $this->ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::RENDERER_BEFORE_RENDER, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'))
            ->will($this->throwException(new AccessDeniedException('Game over man, game over')));

        $this->ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::RENDERER_RENDER, $this->isInstanceOf('Xi\Filelib\Event\RenderEvent'));

        $ret = $this->renderer->render('xooxoo', 'xooxer');

        $this->assertInstanceOf('Xi\Filelib\Renderer\Response', $ret);
        $this->assertSame('', $ret->getContent());
        $this->assertSame(403, $ret->getStatusCode());
        $this->assertEquals(array(), $ret->getHeaders());

    }

    /**
     * @test
     */
    public function versionNotFoundShouldLeadTo404()
    {
        $file = $this->getMockedFile();

        $this->ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::RENDERER_BEFORE_RENDER, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $this->ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::RENDERER_RENDER, $this->isInstanceOf('Xi\Filelib\Event\RenderEvent'));

        $this->pm
            ->expects($this->once())
            ->method('hasVersion')
            ->with($file, Version::get('xooxer'))
            ->will($this->returnValue(false));

        $ret = $this->renderer->render($file, 'xooxer');

        $this->assertInstanceOf('Xi\Filelib\Renderer\Response', $ret);
        $this->assertSame('', $ret->getContent());
        $this->assertSame(404, $ret->getStatusCode());
        $this->assertEquals(array(), $ret->getHeaders());

    }

    /**
     * @return array
     */
    public function provideOptions()
    {
        return array(
            array(false, true, false, true),
            array(true, false, false, true),
            array(false, true, true, false),
            array(true, false, true, true),
            array(false, true, true, false),
            array(true, false, true, true),
        );
    }


    /**
     * @test
     * @dataProvider provideOptions
     */
    public function shouldSetupResponseCorrectly($download, $sharedVersions, $lazy, $doVersionsExist)
    {
        $resource = Resource::create();
        $file = File::create(
            array(
                'resource' => $resource, 'name' => 'lussuti.pdf'
            )
        );

        if ($doVersionsExist) {
            $file->addVersion(Version::get('xooxer'));
            $resource->addVersion(Version::get('xooxer'));
        }

        $this->ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::RENDERER_BEFORE_RENDER, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $this->ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::RENDERER_RENDER, $this->isInstanceOf('Xi\Filelib\Event\RenderEvent'));

        $this->pm
            ->expects($this->once())
            ->method('hasVersion')
            ->with($file, Version::get('xooxer')
            )
            ->will($this->returnValue(true));

        $this->storage
            ->expects($this->once())
            ->method('retrieveVersion')
            ->with(($sharedVersions) ? $resource : $file, Version::get('xooxer'))
            ->will($this->returnValue(ROOT_TESTS . '/data/refcard.pdf'));

        $vp = $this->getMockedVersionProvider(array('xooxer'), $lazy);
        $vp
            ->expects($this->any())
            ->method('getApplicableStorable')
            ->will($this->returnValue($sharedVersions ? $resource : $file));

        $vp->expects($this->any())
            ->method('isValidVersion')
            ->with($this->equalTo(Version::get('xooxer')))
            ->will($this->returnValue(true));

        $this->pm
            ->expects($this->any())
            ->method('getVersionProvider')
            ->with($file, Version::get('xooxer'))
            ->will($this->returnValue($vp));

        if ($lazy) {
            if ($doVersionsExist) {
                $vp->expects($this->never())->method('provideVersion');
            } else {
                $vp
                    ->expects($this->once())
                    ->method('provideVersion')
                    ->with($file, $this->equalTo(Version::get('xooxer')));
            }
        }

        $ret = $this->renderer->render($file, 'xooxer', array('download' => $download));

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

}
