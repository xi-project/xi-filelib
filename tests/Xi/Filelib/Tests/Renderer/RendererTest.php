<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\Renderer\Renderer;
use Xi\Filelib\Renderer\Events;
use Xi\Filelib\Authorization\AccessDeniedException;
use Xi\Filelib\File\File;


class RendererTest extends \Xi\Filelib\Tests\TestCase
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
            ->method('returnResponse')
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
            ->expects($this->once())
            ->method('dispatch')
            ->with(Events::RENDERER_BEFORE_RENDER, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'))
            ->will($this->throwException(new AccessDeniedException('Game over man, game over')));

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
            ->expects($this->once())
            ->method('dispatch')
            ->with(Events::RENDERER_BEFORE_RENDER, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $this->pm
            ->expects($this->once())
            ->method('hasVersion')
            ->with($file, 'xooxer')
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
            array(false, true),
            array(true, false),
        );
    }


    /**
     * @test
     * @dataProvider provideOptions
     */
    public function shouldSetupResponseCorrectly($download, $sharedVersions)
    {
        $resource = $this->getMockedResource();
        $file = File::create(array('resource' => $resource, 'name' => 'lussuti.pdf'));

        $this->ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::RENDERER_BEFORE_RENDER, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $this->ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::RENDERER_RENDER, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

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



    protected function getAdapter()
    {
        return $this->getMock('Xi\Filelib\Renderer\RendererAdapter');
    }

    protected function getRenderer($adapter)
    {
        $renderer = new Renderer(
            $this->filelib,
            $adapter
        );

        return $renderer;
    }




}
