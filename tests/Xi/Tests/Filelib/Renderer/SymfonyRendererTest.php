<?php

namespace Xi\Tests\Filelib\Renderer;

use Xi\Filelib\Renderer\SymfonyRenderer;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SymfonyRendererTest extends \Xi\Tests\Filelib\TestCase
{

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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher', 'getAcl'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('fileIsReadable')->will($this->returnValue(false));

        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));

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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
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

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
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

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher', 'getAcl', 'getStorage'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();

        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $eventDispatcher->expects($this->once())->method('dispatch')
            ->with('file.render', $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $filelib->expects($this->any())->method('getEventDispatcher')
            ->will($this->returnValue($eventDispatcher));

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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher', 'getAcl'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();

        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));

        $fiop->expects($this->any())->method('hasVersion')->will($this->returnValue(false));

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));

        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));

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

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher', 'getAcl', 'getStorage'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();

        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));

        $vp = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $vp->expects($this->any())->method('getIdentifier')->will($this->returnValue('xooxer'));

        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('retrieveVersion')
                ->with($this->equalTo($file), $this->equalTo('lussenhofer'))
                ->will($this->returnValue($retrieved));

        $fiop->expects($this->atLeastOnce())->method('getVersionProvider')
             ->with($this->equalTo($file), $this->equalTo('lussenhofer'))
             ->will($this->returnValue($vp));

        $fiop->expects($this->any())->method('hasVersion')->will($this->returnValue(true));

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->atLeastOnce())->method('isFileReadable')->will($this->returnValue(true));

        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        $renderer->expects($this->any())->method('getStorage')->will($this->returnValue($storage));

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
         $filelib = $this->getMock('Xi\Filelib\FileLibrary');
         $renderer = new SymfonyRenderer($filelib);
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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher', 'getAcl', 'getStorage'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();
        $renderer->enableAcceleration(true);

        $server = array(
            'SERVER_SOFTWARE' => $serverSignature,
        );

        $request = new Request(array(), array(), array(), array(), array(), $server);

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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher', 'getAcl', 'getStorage'))
                         ->setConstructorArgs(array($filelib))
                         ->getMock();
        $renderer->enableAcceleration(false);

        $server = array(
            'SERVER_SOFTWARE' => 'nginx/1.0.10'
        );

        $request = new Request(array(), array(), array(), array(), array(), $server);

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

