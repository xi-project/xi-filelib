<?php

namespace Xi\Tests\Filelib\Renderer;

use Xi\Filelib\Renderer\ZendRenderer;
use Xi\Filelib\File\FileItem;
use Xi\Filelib\File\FileObject;
use Zend_Controller_Request_Http as Request;
use Zend_Controller_Response_Http as Response;

class ZendRendererTest extends \Xi\Tests\Filelib\TestCase
{


    public function setUp()
    {
        if (!class_exists('Zend_Controller_Response_Http')) {
            $this->markTestSkipped('Zend_Controller classes not found');
        }
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
    public function mergeOptionsShouldReturnSanitizedResult()
    {

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = new ZendRenderer($filelib);

        $expected = array(
            'version' => 'original',
            'download' => false,
        );

        $options = array();

        $this->assertEquals($expected, $renderer->mergeOptions($options));

        $expected = array(
            'version' => 'orignaluss',
            'download' => false,
            'impossible' => 'impossibru'
        );

        $options = array(
            'version' => 'orignaluss',
            'impossible' => 'impossibru',
        );

        $this->assertEquals($expected, $renderer->mergeOptions($options));



    }

    /**
     * @test
     */
    public function stripPrefixFromAcceleratedPathShouldDefaultToEmptyString()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');
         $renderer = new ZendRenderer($filelib);

         $this->assertEquals('', $renderer->getStripPrefixFromAcceleratedPath());

    }

    /**
     * @test
     */
    public function stripPrefixFromAcceleratedPathShouldObeySetter()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');
         $renderer = new ZendRenderer($filelib);

         $this->assertEquals('', $renderer->getStripPrefixFromAcceleratedPath());

         $renderer->setStripPrefixFromAcceleratedPath('luss');

         $this->assertEquals('luss', $renderer->getStripPrefixFromAcceleratedPath());
    }


        /**
     * @test
     */
    public function addPrefixToAcceleratedPathShouldDefaultToEmptyString()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');
         $renderer = new ZendRenderer($filelib);

         $this->assertEquals('', $renderer->getAddPrefixToAcceleratedPath());

    }

    /**
     * @test
     */
    public function addPrefixToAcceleratedPathShouldObeySetter()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');
         $renderer = new ZendRenderer($filelib);

         $this->assertSame('', $renderer->getAddPrefixToAcceleratedPath());

         $renderer->setAddPrefixToAcceleratedPath('luss');

         $this->assertSame('luss', $renderer->getAddPrefixToAcceleratedPath());
    }




    /**
     * @test
     */
    public function getPublisherShouldDelegateToFilelib()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = new ZendRenderer($filelib);

        $filelib->expects($this->once())->method('getPublisher');

        $renderer = $renderer->getPublisher();
    }




    /**
     * @test
     */
    public function getAclShouldDelegateToFilelib()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = new ZendRenderer($filelib);

        $filelib->expects($this->once())->method('getAcl');

        $acl = $renderer->getAcl();
    }


    /**
     * @test
     */
    public function getStorageShouldDelegateToFilelib()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = new ZendRenderer($filelib);

        $filelib->expects($this->once())->method('getStorage');

        $acl = $renderer->getStorage();
    }

    /**
     * @test
     */
    public function getUrlShouldDelegateToPublisherWhenUsingOriginalVersion()
    {
        $file = FileItem::create(array('id' => 1));

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\ZendRenderer')
                         ->setMethods(array('getPublisher'))
                         ->setConstructorArgs(array($filelib))
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
        $file = FileItem::create(array('id' => 1));

        $vp = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));

        $fiop->expects($this->once())->method('getVersionProvider')
             ->with($this->equalTo($file), $this->equalTo('lussen'))
             ->will($this->returnValue($vp));


        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\ZendRenderer')
                         ->setMethods(array('getPublisher'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();

        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $publisher->expects($this->once())->method('getUrlVersion')->with($this->equalTo($file), $this->equalTo('lussen'), $this->equalTo($vp));

        $renderer->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));

        $url = $renderer->getUrl($file, array('version' => 'lussen'));

    }

    /**
     * @test
     */
    public function responseShouldBe403WhenAclForbidsRead()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\ZendRenderer')
                         ->setMethods(array('getPublisher', 'getAcl'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('fileIsReadable')->will($this->returnValue(false));

        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));

        $file = FileItem::create(array('id' => 1));

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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\ZendRenderer')
                         ->setMethods(array('getPublisher', 'getAcl'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(false));

        $fiop->expects($this->any())->method('getProfile')->will($this->returnValue($profile));

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));

        $file = FileItem::create(array('id' => 1));

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

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\ZendRenderer')
                         ->setMethods(array('getPublisher', 'getAcl', 'getStorage'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();

        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(true));

        $retrieved = new FileObject($path);
        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('retrieve')->will($this->returnValue($retrieved));

        $fiop->expects($this->any())->method('getProfile')->will($this->returnValue($profile));


        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        $renderer->expects($this->any())->method('getStorage')->will($this->returnValue($storage));

        $file = FileItem::create(array('id' => 1));

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

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\ZendRenderer')
                         ->setMethods(array('getPublisher', 'getAcl', 'getStorage'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();

        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(true));

        $retrieved = new FileObject($path);
        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('retrieve')->will($this->returnValue($retrieved));

        $fiop->expects($this->any())->method('getProfile')->will($this->returnValue($profile));

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        $renderer->expects($this->any())->method('getStorage')->will($this->returnValue($storage));

        $file = FileItem::create(array('id' => 1, 'name' => 'self-lusser.lus'));

        $response = $renderer->render($file, array('download' => true));

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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\ZendRenderer')
                         ->setMethods(array('getPublisher', 'getAcl'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();

        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));

        $fiop->expects($this->any())->method('hasVersion')->will($this->returnValue(false));

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));

        $file = FileItem::create(array('id' => 1));

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

        $file = FileItem::create(array('id' => 1));

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\ZendRenderer')
                         ->setMethods(array('getPublisher', 'getAcl', 'getStorage'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();

        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));

        $vp = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');

        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('retrieveVersion')
                ->with($this->equalTo($file), $this->equalTo('lussenhofer'))
                ->will($this->returnValue($retrieved));

        $fiop->expects($this->any())->method('hasVersion')->will($this->returnValue(true));

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->atLeastOnce())->method('isFileReadable')->will($this->returnValue(true));

        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        $renderer->expects($this->any())->method('getStorage')->will($this->returnValue($storage));

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
    public function accelerationShouldBeDisabledByDefault()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');
         $renderer = new ZendRenderer($filelib);

         $this->assertFalse($renderer->isAccelerationEnabled());

    }

    /**
     * @test
     */
    public function enableAccelerationShouldEnableAcceleration()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');
         $renderer = new ZendRenderer($filelib);

         $this->assertFalse($renderer->isAccelerationEnabled());

         $renderer->enableAcceleration(true);

         $this->assertTrue($renderer->isAccelerationEnabled());
    }



    /**
     * @test
     */
    public function accelerationShouldNotBePossibleWithoutRequestAsContext()
    {
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');
         $renderer = new ZendRenderer($filelib);
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

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = new ZendRenderer($filelib);
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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\ZendRenderer')
                         ->setMethods(array('getPublisher', 'getAcl', 'getStorage'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();
        $renderer->enableAcceleration(true);

        $server = array(
            'SERVER_SOFTWARE' => $serverSignature,
        );


        $_SERVER['SERVER_SOFTWARE'] = $serverSignature;
        $request = new \Zend_Controller_Request_HttpTestCase();

        $this->assertNull($renderer->getRequest());

        $renderer->setRequest($request);
        $this->assertTrue($renderer->isAccelerationPossible());

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(true));

        $retrieved = new FileObject($path);
        $storage = $this->getMock('Xi\Filelib\Storage\FilesystemStorage');
        $storage->expects($this->once())->method('retrieve')->will($this->returnValue($retrieved));
        $storage->expects($this->any())->method('getRoot')->will($this->returnValue(ROOT_TESTS));

        $fiop->expects($this->any())->method('getProfile')->will($this->returnValue($profile));

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        $renderer->expects($this->any())->method('getStorage')->will($this->returnValue($storage));

        $file = FileItem::create(array('id' => 1, 'name' => 'self-lusser.lus'));

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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\ZendRenderer')
                         ->setMethods(array('getPublisher', 'getAcl', 'getStorage'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();
        $renderer->enableAcceleration(false);

        $request = new \Zend_Controller_Request_HttpTestCase();
        $_SERVER['SERVER_SOFTWARE'] = 'nginx/1.0.10';

        $this->assertNull($renderer->getRequest());

        $renderer->setRequest($request);
        $this->assertTrue($renderer->isAccelerationPossible());

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(true));

        $retrieved = new FileObject($path);
        $storage = $this->getMock('Xi\Filelib\Storage\FilesystemStorage');
        $storage->expects($this->once())->method('retrieve')->will($this->returnValue($retrieved));
        $storage->expects($this->any())->method('getRoot')->will($this->returnValue(ROOT_TESTS));

        $fiop->expects($this->any())->method('getProfile')->will($this->returnValue($profile));

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        $renderer->expects($this->any())->method('getStorage')->will($this->returnValue($storage));

        $file = FileItem::create(array('id' => 1, 'name' => 'self-lusser.lus'));

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

