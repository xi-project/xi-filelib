<?php

namespace Xi\Filelib\Tests\File\Upload;

use Xi\Filelib\File\Upload\Limiter;
use Xi\Filelib\Tests\TestCase;

use \Xi\Filelib\File\Upload\FileUpload;

class LimiterTest extends TestCase
{

    /**
     * @test
     */
    public function limiterShouldBeEmptyWhenInitialized()
    {

        $limiter = new Limiter();

        $this->assertEquals(array(), $limiter->getAccepted());
        $this->assertEquals(array(), $limiter->getDenied());

    }

    /**
     * @test
     */
    public function interfaceShouldBeFluent()
    {

        $limiter = new Limiter();

        $this->assertEquals($limiter, $limiter->accept('lussen/hof'));
        $this->assertEquals($limiter, $limiter->deny('slussen/lus'));

    }

    /**
     * @test
     */
    public function limiterShouldAcceptAnythingWhenEmpty()
    {
        $limiter = new Limiter();
        $this->assertTrue($limiter->isAccepted($this->getUpload('lussen/lus')));

        $this->assertTrue($limiter->isAccepted($this->getUpload('lussen/tus')));
    }

    private function getUpload($mimeType)
    {
       $upload = $this->getMockBuilder('\Xi\Filelib\File\Upload\FileUpload')->setConstructorArgs(array(ROOT_TESTS . '/data/self-lussing-manatee.jpg'))->getMock();
       $upload->expects($this->once())->method('getMimeType')->will($this->returnValue($mimeType));

       return $upload;
    }

    /**
     * @test
     */
    public function limiterShouldAcceptAnythingButExplicitlyDeniedWhenOnlyAcceptedIsEmpty()
    {
        $limiter = new Limiter();
        $limiter->deny("^image/png$");

        $this->assertFalse($limiter->isAccepted($this->getUpload('image/png')));

        $this->assertTrue($limiter->isAccepted($this->getUpload('image/jpg')));
        $this->assertTrue($limiter->isAccepted($this->getUpload('lussen/tus')));
    }

    /**
     * @test
     */
    public function limiterShouldDenyAnythingButExplicitlyDeniedWhenOnlyDeniedIsEmpty()
    {
        $limiter = new Limiter();
        $limiter->accept("^image/png$");

        $this->assertTrue($limiter->isAccepted($this->getUpload('image/png')));

        $this->assertFalse($limiter->isAccepted($this->getUpload('image/jpg')));
        $this->assertFalse($limiter->isAccepted($this->getUpload('lussen/tus')));
    }

    /**
     * @test
     */
    public function limiterShouldUnderstandRegexes()
    {
        $limiter = new Limiter();
        $limiter->deny("^image");

        $this->assertFalse($limiter->isAccepted($this->getUpload('image/png')));
        $this->assertFalse($limiter->isAccepted($this->getUpload('image/jpg')));
        $this->assertTrue($limiter->isAccepted($this->getUpload('lussen/tus')));
    }

    /**
     * @test
     */
    public function acceptShouldOverrideDeny()
    {
        $limiter = new Limiter();
        $limiter->deny("^image");

        $this->assertFalse($limiter->isAccepted($this->getUpload('image/png')));
        $this->assertFalse($limiter->isAccepted($this->getUpload('image/jpg')));

        $limiter->accept("^image");

        $this->assertTrue($limiter->isAccepted($this->getUpload('image/png')));
        $this->assertTrue($limiter->isAccepted($this->getUpload('image/jpg')));

    }

    /**
     * @test
     */
    public function denyShouldOverrideAccept()
    {
        $limiter = new Limiter();
        $limiter->accept("^image");

        $this->assertTrue($limiter->isAccepted($this->getUpload('image/png')));
        $this->assertTrue($limiter->isAccepted($this->getUpload('image/jpg')));

        $limiter->deny("^image");

        $this->assertFalse($limiter->isAccepted($this->getUpload('image/png')));
        $this->assertFalse($limiter->isAccepted($this->getUpload('image/jpg')));

    }

    /**
     * @test
     */
    public function getAcceptedShouldWorkAsExpected()
    {
        $limiter = new Limiter();

        $acceptore = array("^image", "^video");

        $limiter->accept($acceptore);

        $acceptore2 = array("[^image]" => "[^image]", "[^video]" => "[^video]");

        $this->assertEquals($acceptore2, $limiter->getAccepted());

    }

    /**
     * @test
     */
    public function getDeniedShouldWorkAsExpected()
    {
        $limiter = new Limiter();

        $deniatore = array("^image", "^video");

        $limiter->accept($deniatore);

        $deniatore2 = array("[^image]" => "[^image]", "[^video]" => "[^video]");

        $this->assertEquals($deniatore2, $limiter->getAccepted());

    }

    /**
     * @test
     */
    public function limiterShouldDenyFirstWhenNeitherDeniedOrAcceptedIsEmpty()
    {
        $limiter = new Limiter();
        $limiter->deny("^image");
        $limiter->accept("^video");

        $this->assertFalse($limiter->isAccepted($this->getUpload('image/png')));

        $this->assertTrue($limiter->isAccepted($this->getUpload('video/lus')));

        $this->assertFalse($limiter->isAccepted($this->getUpload('lussen/tus')));
    }

}
