<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Renderer\SymfonyRenderer;
use Symfony\Component\HttpFoundation\Request;

class SymfonyRendererTest extends \Xi\Filelib\Tests\TestCase
{

    protected $filelib;

    protected $fiop;

    protected $acl;

    protected $profile;

    protected $storage;

    protected $eventDispatcher;

    protected $configuration;

    protected $publisher;

    public function setUp()
    {
        $this->profile = $this->getMockedFileProfile();
        $this->fiop = $this->getMockedFileOperator();
        $this->fiop
            ->expects($this->any())
            ->method('getProfile')
            ->will($this->returnValue($this->profile));

        $this->acl = $this->getMockedAcl();
        $this->storage = $this
            ->getMockBuilder('Xi\Filelib\Storage\FilesystemStorage')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventDispatcher = $this->getMockedEventDispatcher();
        $this->publisher = $this->getMockedPublisher();

    }

    private function getMockedRenderer($methods = array())
    {
        return new SymfonyRenderer(
            $this->storage,
            $this->publisher,
            $this->acl,
            $this->eventDispatcher,
            $this->fiop
        );
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Renderer\SymfonyRenderer'));
        $this->assertContains(
            'Xi\Filelib\Renderer\AcceleratedRenderer',
            class_implements('Xi\Filelib\Renderer\SymfonyRenderer')
        );
    }

    /**
     * @test
     */
    public function responseShouldBe403WhenAclForbidsRead()
    {

        $renderer = $this->getMockedRenderer();
        $this->acl
            ->expects($this->any())
            ->method('fileIsReadable')
            ->will($this->returnValue(false));

        $file = File::create(array('id' => 1, 'resource' => Resource::create()));

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
        $renderer = $this->getMockedRenderer();

        $this->profile
            ->expects($this->atLeastOnce())
            ->method('getAccessToOriginal')
            ->will($this->returnValue(false));

        $this->acl
            ->expects($this->any())
            ->method('isFileReadable')
            ->will($this->returnValue(true));

        $file = File::create(array('id' => 1, 'resource' => Resource::create()));

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

        $renderer = $this->getMockedRenderer();

        $this->profile
            ->expects($this->atLeastOnce())
            ->method('getAccessToOriginal')
            ->will($this->returnValue(true));

        $retrieved = new FileObject($path);
        $this->storage
            ->expects($this->once())
            ->method('retrieve')
            ->will($this->returnValue($retrieved));

        $this->acl
            ->expects($this->any())
            ->method('isFileReadable')
            ->will($this->returnValue(true));

        $file = File::create(array('id' => 1, 'resource' => Resource::create()));

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

        $renderer = $this->getMockedRenderer();

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                'xi_filelib.file.render',
                $this->isInstanceOf('Xi\Filelib\Event\FileEvent')
            );

        $this->profile
            ->expects($this->atLeastOnce())
            ->method('getAccessToOriginal')
            ->will($this->returnValue(true));

        $retrieved = new FileObject($path);
        $this->storage
            ->expects($this->once())
            ->method('retrieve')
            ->will($this->returnValue($retrieved));

        $this->acl
            ->expects($this->any())
            ->method('isFileReadable')
            ->will($this->returnValue(true));

        $file = File::create(array('id' => 1, 'name' => 'self-lusser.lus', 'resource' => Resource::create()));

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
        $renderer = $this->getMockedRenderer();

        $this->fiop
            ->expects($this->any())
            ->method('hasVersion')
            ->will($this->returnValue(false));

        $this->acl
            ->expects($this->any())
            ->method('isFileReadable')
            ->will($this->returnValue(true));

        $file = File::create(array('id' => 1, 'resource' => Resource::create()));

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

        $resource = Resource::create();
        $file = File::create(array('id' => 1, 'resource' => $resource));

        $renderer = $this->getMockedRenderer();

        $vp = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $vp->expects($this->any())->method('getIdentifier')->will($this->returnValue('xooxer'));

        $this->storage
            ->expects($this->once())
            ->method('retrieveVersion')
            ->with($this->equalTo($resource), $this->equalTo('lussenhofer'))
            ->will($this->returnValue($retrieved));

        $this->fiop
            ->expects($this->atLeastOnce())
            ->method('getVersionProvider')
            ->with($this->equalTo($file), $this->equalTo('lussenhofer'))
            ->will($this->returnValue($vp));

        $this->fiop
            ->expects($this->any())
            ->method('hasVersion')
            ->will($this->returnValue(true));

        $this->acl
            ->expects($this->atLeastOnce())
            ->method('isFileReadable')
            ->will($this->returnValue(true));

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
         $renderer = $this->getMockedRenderer();
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

        $renderer = $this->getMockedRenderer();
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
        $renderer = $this->getMockedRenderer();
        $renderer->enableAcceleration(true);

        $server = array(
            'SERVER_SOFTWARE' => $serverSignature,
        );

        $request = new Request(array(), array(), array(), array(), array(), $server);

        $this->assertNull($renderer->getRequest());

        $renderer->setRequest($request);
        $this->assertTrue($renderer->isAccelerationPossible());
        $this->assertTrue($renderer->isAccelerationEnabled());

        $this->profile
            ->expects($this->atLeastOnce())
            ->method('getAccessToOriginal')
            ->will($this->returnValue(true));

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $retrieved = new FileObject($path);
        $this->storage
            ->expects($this->once())
            ->method('retrieve')
            ->will($this->returnValue($retrieved));

        $this->storage
            ->expects($this->any())
            ->method('getRoot')
            ->will($this->returnValue(ROOT_TESTS));

        $this->acl
            ->expects($this->atLeastOnce())
            ->method('isFileReadable')
            ->will($this->returnValue(true));

        $file = File::create(array('id' => 1, 'name' => 'self-lusser.lus', 'resource' => Resource::create()));

        $renderer->setStripPrefixFromAcceleratedPath($this->storage->getRoot());
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
        $renderer = $this->getMockedRenderer();
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

        $this->profile
            ->expects($this->atLeastOnce())
            ->method('getAccessToOriginal')
            ->will($this->returnValue(true));

        $retrieved = new FileObject($path);
        $this->storage
            ->expects($this->once())
            ->method('retrieve')
            ->will($this->returnValue($retrieved));

        $this->storage
            ->expects($this->any())
            ->method('getRoot')
            ->will($this->returnValue(ROOT_TESTS));

        $this->acl
            ->expects($this->any())
            ->method('isFileReadable')
            ->will($this->returnValue(true));

        $file = File::create(array('id' => 1, 'name' => 'self-lusser.lus', 'resource' => Resource::create()));

        $renderer->setStripPrefixFromAcceleratedPath($this->storage->getRoot());
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
