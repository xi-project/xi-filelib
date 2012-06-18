<?php

namespace Xi\Tests\Filelib\Renderer;

use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Renderer\SymfonyRenderer;
use Symfony\Component\HttpFoundation\Request;

class SymfonyRendererTest extends \Xi\Tests\Filelib\TestCase
{

    protected $filelib;

    protected $fiop;

    protected $acl;

    protected $profile;

    protected $storage;

    protected $eventDispatcher;

    public function setUp()
    {
        $this->filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $this->fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $this->filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($this->fiop));

        $this->profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $this->fiop->expects($this->any())->method('getProfile')->will($this->returnValue($this->profile));

        $this->acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');

        $this->storage = $this->getMock('Xi\Filelib\Storage\Storage');

        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->filelib->expects($this->any())->method('getEventDispatcher')
            ->will($this->returnValue($this->eventDispatcher));


    }

    private function getMockedRenderer($methods = array())
    {
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
            ->setMethods($methods)
            ->setConstructorArgs(array($this->filelib))
            ->getMock();
        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($this->acl));
        $renderer->expects($this->any())->method('getStorage')->will($this->returnValue($this->storage));

        return $renderer;
    }


    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Renderer\SymfonyRenderer'));
        $this->assertContains('Xi\Filelib\Renderer\AcceleratedRenderer', class_implements('Xi\Filelib\Renderer\SymfonyRenderer'));
    }


    /**
     * @test
     */
    public function responseShouldBe403WhenAclForbidsRead()
    {

        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl'));
        $this->acl->expects($this->any())->method('fileIsReadable')->will($this->returnValue(false));

        $file = File::create(array('id' => 1));

        $response = $renderer->render($file);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());

    }



    /**
     * @test
     */
    public function responseShouldBe403WhenProfileForbidsReadOfOriginalFile()
    {
        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl'));
        $this->profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(false));

        $this->acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $file = File::create(array('id' => 1));

        $response = $renderer->render($file);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());

    }

    /**
     * @test
     */
    public function responseShouldBeCorrectWhenProfileAllowsReadOfOriginalFileAndDownloadIsFalse()
    {
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl', 'getStorage'));

        $this->profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(true));

        $retrieved = new FileObject($path);
        $this->storage->expects($this->once())->method('retrieve')->will($this->returnValue($retrieved));

        $this->acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $file = File::create(array('id' => 1));

        $response = $renderer->render($file);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals(file_get_contents($path), $response->getContent());
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));


    }


    /**
     * @test
     */
    public function responseShouldBeCorrectWhenProfileAllowsReadOfOriginalFileAndDownloadIsTrue()
    {
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl', 'getStorage'));

        $this->eventDispatcher->expects($this->once())->method('dispatch')
            ->with('file.render', $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $this->profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(true));

        $retrieved = new FileObject($path);
        $this->storage->expects($this->once())->method('retrieve')->will($this->returnValue($retrieved));

        $this->acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $file = File::create(array('id' => 1, 'name' => 'self-lusser.lus'));

        $response = $renderer->render($file, array('download' => true, 'track' => true));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals(file_get_contents($path), $response->getContent());
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));

        $this->assertEquals("attachment; filename=self-lusser.lus", $response->headers->get('Content-disposition'));

    }



    /**
     * @test
     */
    public function responseShouldBe404WhenVersionDoesNotExist()
    {

        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl'));

        $this->fiop->expects($this->any())->method('hasVersion')->will($this->returnValue(false));

        $this->acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $file = File::create(array('id' => 1));

        $response = $renderer->render($file, array('version' => 'lussenhofer'));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());
    }

    /**
     * @test
     */
    public function responseShouldBeCorrectWhenVersionDoesExist()
    {
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $retrieved = new FileObject($path);

        $file = File::create(array('id' => 1));

        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl', 'getStorage'));

        $vp = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $vp->expects($this->any())->method('getIdentifier')->will($this->returnValue('xooxer'));

        $this->storage->expects($this->once())->method('retrieveVersion')
                ->with($this->equalTo($file), $this->equalTo('lussenhofer'))
                ->will($this->returnValue($retrieved));

        $this->fiop->expects($this->atLeastOnce())->method('getVersionProvider')
             ->with($this->equalTo($file), $this->equalTo('lussenhofer'))
             ->will($this->returnValue($vp));

        $this->fiop->expects($this->any())->method('hasVersion')->will($this->returnValue(true));

        $this->acl->expects($this->atLeastOnce())->method('isFileReadable')->will($this->returnValue(true));

        $response = $renderer->render($file, array('version' => 'lussenhofer'));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals(file_get_contents($path), $response->getContent());
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));

    }

    /**
     * @test
     */
    public function accelerationShouldNotBePossibleWithoutRequestAsContext()
    {
         $renderer = new SymfonyRenderer($this->filelib);
         $renderer->enableAcceleration(true);

         $this->assertNull($renderer->getRequest());
         $this->assertTrue($renderer->isAccelerationEnabled());

         $this->assertFalse($renderer->isAccelerationPossible());

    }

    public function provideBadServerSignatures()
    {
        return array(
            array(
                false,
                array()
            ),
            array(
                false,
                array(
                    'SERVER_SOFTWARE' => 'Microsoft-IIS/5.0'
                )
            ),
            array(
                true,
                array(
                    'SERVER_SOFTWARE' => 'nginx/1.0.10'
                )
            ),
        );

    }

    /**
     * @test
     * @dataProvider provideBadServerSignatures
     */
    public function possibilityOfAccelerationsShouldDependOnServerSignature($expected, $server)
    {
        $request = new Request(array(), array(), array(), array(), array(), $server);

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = new SymfonyRenderer($filelib);
        $renderer->enableAcceleration(true);

        $renderer->setRequest($request);

        $this->assertEquals($expected, $renderer->isAccelerationPossible());

    }


    public function provideServersForAccelerationTest()
    {
        return array(
            array(
                'x-accel-redirect',
                'nginx/1.0.11'
            ),
            array(
                'x-sendfile',
                'Cherokee/1.2',
            ),
            array(
                'x-sendfile',
                'lighttpd/2.0.0',
            ),
            array(
                'x-sendfile',
                'lighttpd/1.5',
            ),
            array(
                'x-lighttpd-send-file',
                'lighttpd/1.4',
            ),
            array(
                'x-sendfile',
                'Apache',
            ),
        );
    }


    /**
     * @dataProvider provideServersForAccelerationTest
     * @test
     *
     */
    public function acceleratedRequestShouldBeEmptyAndContainCorrectHeaders($expectedHeader, $serverSignature)
    {
        $this->storage = $this->getMock('Xi\Filelib\Storage\FilesystemStorage');

        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl', 'getStorage'));
        $renderer->enableAcceleration(true);

        $server = array(
            'SERVER_SOFTWARE' => $serverSignature,
        );

        $request = new Request(array(), array(), array(), array(), array(), $server);

        $this->assertNull($renderer->getRequest());

        $renderer->setRequest($request);
        $this->assertTrue($renderer->isAccelerationPossible());
        $this->assertTrue($renderer->isAccelerationEnabled());

        $this->profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(true));

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $retrieved = new FileObject($path);
        $this->storage->expects($this->once())->method('retrieve')->will($this->returnValue($retrieved));
        $this->storage->expects($this->any())->method('getRoot')->will($this->returnValue(ROOT_TESTS));

        $this->acl->expects($this->atLeastOnce())->method('isFileReadable')->will($this->returnValue(true));

        $file = File::create(array('id' => 1, 'name' => 'self-lusser.lus'));

        $renderer->setStripPrefixFromAcceleratedPath($renderer->getStorage()->getRoot());
        $renderer->setAddPrefixToAcceleratedPath('/protected/files');
        $response = $renderer->render($file, array('download' => false));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty($response->getContent());

        $this->assertArrayHasKey($expectedHeader, $response->headers->all());

        $this->assertEquals('/protected/files/data/self-lussing-manatee.jpg', $response->headers->get($expectedHeader));


    }

    /**
     * @test
     */
    public function acceleratedRequestShouldNotBeEmptyAndNotContainHeadersWhenAccelerationIsDisabled()
    {
        $this->storage = $this->getMock('Xi\Filelib\Storage\FilesystemStorage');
        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl', 'getStorage'));
        $renderer->enableAcceleration(false);

        $server = array(
            'SERVER_SOFTWARE' => 'nginx/1.0.10'
        );

        $request = new Request(array(), array(), array(), array(), array(), $server);

        $this->assertNull($renderer->getRequest());

        $renderer->setRequest($request);
        $this->assertTrue($renderer->isAccelerationPossible());
        $this->assertFalse($renderer->isAccelerationEnabled());

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $this->profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(true));

        $retrieved = new FileObject($path);
        $this->storage->expects($this->once())->method('retrieve')->will($this->returnValue($retrieved));
        $this->storage->expects($this->any())->method('getRoot')->will($this->returnValue(ROOT_TESTS));

        $this->acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $file = File::create(array('id' => 1, 'name' => 'self-lusser.lus'));

        $renderer->setStripPrefixFromAcceleratedPath($renderer->getStorage()->getRoot());
        $renderer->setAddPrefixToAcceleratedPath('/protected/files');
        $response = $renderer->render($file, array('download' => false));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());

        $this->assertArrayNotHasKey('x-sendfile', $response->headers->all());
        $this->assertArrayNotHasKey('x-lighttpd-send-file', $response->headers->all());
        $this->assertArrayNotHasKey('x-accel-redirect', $response->headers->all());

    }

}

