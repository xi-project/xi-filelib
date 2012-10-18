<?php

namespace Xi\Tests\Filelib\Renderer;

use Xi\Filelib\Renderer\ZendRenderer;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\FileObject;
use Zend_Controller_Request_Http as Request;
use Zend_Controller_Response_Http as Response;

class ZendRendererTest extends \Xi\Tests\Filelib\TestCase
{

    protected $filelib;

    protected $fiop;

    protected $acl;

    protected $profile;

    protected $storage;

    protected $eventDispatcher;

    public function setUp()
    {
        if (!class_exists('Zend_Controller_Response_Http')) {
            $this->markTestSkipped('Zend_Controller classes not found');
        }

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

    /**
     * @param array $methods
     * @return SymfonyRenderer
     */
    private function getMockedRenderer($methods = array())
    {
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\ZendRenderer')
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
        $this->assertTrue(class_exists('Xi\Filelib\Renderer\ZendRenderer'));
        $this->assertContains('Xi\Filelib\Renderer\AcceleratedRenderer', class_implements('Xi\Filelib\Renderer\ZendRenderer'));
    }


    /**
     * @test
     */
    public function responseShouldBe403WhenAclForbidsRead()
    {
        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl'));
        $this->acl->expects($this->any())->method('fileIsReadable')->will($this->returnValue(false));

        $file = File::create(array('id' => 1, 'resource' => Resource::create()));

        $response = $renderer->render($file);

        $this->assertInstanceOf('Zend_Controller_Response_Http', $response);

        $this->assertEquals(403, $response->getHttpResponseCode());
        $this->assertNotEmpty($response->getBody());

    }



    /**
     * @test
     */
    public function responseShouldBe403WhenProfileForbidsReadOfOriginalFile()
    {
        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl'));
        $this->profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(false));
        $this->acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));


        $file = File::create(array('id' => 1, 'resource' => Resource::create()));

        $response = $renderer->render($file);

        $this->assertInstanceOf('Zend_Controller_Response_Http', $response);

        $this->assertEquals(403, $response->getHttpResponseCode());
        $this->assertNotEmpty($response->getBody());

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

        $resource = Resource::create();
        $file = File::create(array('id' => 1, 'resource' => $resource));

        $response = $renderer->render($file);

        $this->assertInstanceOf('Zend_Controller_Response_Http', $response);

        $this->assertEquals(200, $response->getHttpResponseCode());
        $this->assertNotEmpty($response->getBody());
        $this->assertEquals(file_get_contents($path), $response->getBody());

        $this->assertHeaderEquals('image/jpeg', 'Content-Type', $response);
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

        $file = File::create(array('id' => 1, 'name' => 'self-lusser.lus', 'resource' => Resource::create()));

        $response = $renderer->render($file, array('download' => true, 'track' => true));

        $this->assertInstanceOf('Zend_Controller_Response_Http', $response);

        $this->assertEquals(200, $response->getHttpResponseCode());
        $this->assertNotEmpty($response->getBody());
        $this->assertEquals(file_get_contents($path), $response->getBody());

        $this->assertHeaderEquals('image/jpeg', 'Content-Type', $response);
        $this->assertHeaderEquals("attachment; filename=self-lusser.lus", 'Content-Disposition', $response);

    }



    /**
     * @test
     */
    public function responseShouldBe404WhenVersionDoesNotExist()
    {
        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl'));
        $this->fiop->expects($this->any())->method('hasVersion')->will($this->returnValue(false));
        $this->acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $file = File::create(array('id' => 1, 'resource' => Resource::create()));

        $response = $renderer->render($file, array('version' => 'lussenhofer'));

        $this->assertInstanceOf('Zend_Controller_Response_Http', $response);

        $this->assertEquals(404, $response->getHttpResponseCode());
        $this->assertNotEmpty($response->getBody());

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

        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl', 'getStorage'));

        $vp = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');

        $this->storage->expects($this->once())->method('retrieveVersion')
                ->with($this->equalTo($resource), $this->equalTo('lussenhofer'))
                ->will($this->returnValue($retrieved));

        $this->fiop->expects($this->any())->method('hasVersion')->will($this->returnValue(true));

        $this->acl->expects($this->atLeastOnce())->method('isFileReadable')->will($this->returnValue(true));

        $response = $renderer->render($file, array('version' => 'lussenhofer'));

        $this->assertInstanceOf('Zend_Controller_Response_Http', $response);

        $this->assertEquals(200, $response->getHttpResponseCode());
        $this->assertNotEmpty($response->getBody());
        $this->assertEquals(file_get_contents($path), $response->getBody());

        $this->assertHeaderEquals('image/jpeg', 'Content-Type', $response);

    }

    /**
     * @test
     */
    public function accelerationShouldNotBePossibleWithoutRequestAsContext()
    {
         $renderer = new ZendRenderer($this->filelib);
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
        if (isset($server['SERVER_SOFTWARE'])) {
            $_SERVER['SERVER_SOFTWARE'] = $server['SERVER_SOFTWARE'];
        }

        $request = new \Zend_Controller_Request_HttpTestCase();

        $renderer = new ZendRenderer($this->filelib);
        $renderer->enableAcceleration(true);
        $renderer->setRequest($request);
        $this->assertEquals($expected, $renderer->isAccelerationPossible());
    }


    public function provideServersForAccelerationTest()
    {
        return array(
            array(
                'X-Accel-Redirect',
                'nginx/1.0.11'
            ),
            array(
                'X-Sendfile',
                'Cherokee/1.2',
            ),
            array(
                'X-Sendfile',
                'lighttpd/2.0.0',
            ),
            array(
                'X-Sendfile',
                'lighttpd/1.5',
            ),
            array(
                'X-Lighttpd-Send-File',
                'lighttpd/1.4',
            ),
            array(
                'X-Sendfile',
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

        $_SERVER['SERVER_SOFTWARE'] = $serverSignature;
        $request = new \Zend_Controller_Request_HttpTestCase();

        $this->assertNull($renderer->getRequest());

        $renderer->setRequest($request);
        $this->assertTrue($renderer->isAccelerationPossible());
        $this->assertTrue($renderer->isAccelerationEnabled());

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $this->profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(true));

        $retrieved = new FileObject($path);
        $this->storage->expects($this->once())->method('retrieve')->will($this->returnValue($retrieved));
        $this->storage->expects($this->any())->method('getRoot')->will($this->returnValue(ROOT_TESTS));

        $this->acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $file = File::create(array('id' => 1, 'name' => 'self-lusser.lus', 'resource' => Resource::create()));

        $renderer->setStripPrefixFromAcceleratedPath($renderer->getStorage()->getRoot());
        $renderer->setAddPrefixToAcceleratedPath('/protected/files');

        $response = $renderer->render($file, array('download' => false));

        $this->assertInstanceOf('Zend_Controller_Response_Http', $response);
        $this->assertEquals(200, $response->getHttpResponseCode());
        $this->assertEmpty($response->getBody());
        $this->assertHeadersContain($expectedHeader, $response->getHeaders());
        $this->assertHeaderEquals('/protected/files/data/self-lussing-manatee.jpg', $expectedHeader, $response);
    }

    /**
     * @test
     */
    public function acceleratedRequestShouldNotBeEmptyAndNotContainHeadersWhenAccelerationIsDisabled()
    {
        $this->storage = $this->getMock('Xi\Filelib\Storage\FilesystemStorage');

        $renderer = $this->getMockedRenderer(array('getPublisher', 'getAcl', 'getStorage'));
        $renderer->enableAcceleration(false);

        $request = new \Zend_Controller_Request_HttpTestCase();
        $_SERVER['SERVER_SOFTWARE'] = 'nginx/1.0.10';

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

        $file = File::create(array('id' => 1, 'name' => 'self-lusser.lus', 'resource' => Resource::create()));

        $renderer->setStripPrefixFromAcceleratedPath($renderer->getStorage()->getRoot());
        $renderer->setAddPrefixToAcceleratedPath('/protected/files');
        $response = $renderer->render($file, array('download' => false));

        $this->assertInstanceOf('Zend_Controller_Response_Http', $response);
        $this->assertEquals(200, $response->getHttpResponseCode());
        $this->assertNotEmpty($response->getBody());
        $this->assertArrayNotHasKey('X-Sendfile', $response->getHeaders());
        $this->assertArrayNotHasKey('X-Lighttpd-Send-File', $response->getHeaders());
        $this->assertArrayNotHasKey('X-Accel-Redirect', $response->getHeaders());
    }


    /**
     * God damn why zend response has no getHeader?!?!? Gaaaaak
     *
     * @param type $expected Expected value
     * @param type $headerName Header name
     * @param Response $response Response
     */
    public function assertHeaderEquals($expected, $headerName, Response $response)
    {
        $headers = $response->getHeaders();
        foreach ($headers as $header) {
            if ($header['name'] == $headerName) {
                if ($header['value'] !== $expected) {
                    $this->fail("Header value '{$header['value']}' does not match expected '{$expected}'");
                } else {
                    return;
                }
            }
        }
        $this->fail("Header '{$headerName}' is not set");
    }

    /**
     * God damn why zend response has no getHeader?!?!? Gaaaaak
     *
     * @param type $headerName Header name
     * @param Response $response Response
     */
    public function assertHeadersContain($headerName, $headers)
    {
        foreach ($headers as $header) {
            if ($header['name'] == $headerName) {
                return;
            }
        }
        $this->fail("Header '{$headerName}' is not set");
    }

}

