<?php

// FIXME and REMOVEME: Use composer (phar, json etc.) to do autoloading...
// Investigate how it's done for PHP 5.2 classes, which use undercored names as a namespace hack.
require_once('/Users/peterhil/code/xi/xi-filelib-symfony-sandbox/vendor/zencoder-php/Services/Zencoder.php');

use Services_Zencoder as ZencoderService;
use Services_Zencoder_Account as Account;
use Services_Zencoder_Exception as ZencoderException;

use \Xi\Filelib\File\FileItem;
use \Xi\Filelib\File\FileObject;
use \Xi\Filelib\FileLibrary;
use \Xi\Filelib\Plugin\Video\ZencoderPlugin;
use \Xi\Filelib\Publisher\Filesystem\SymlinkPublisher;
use \Xi\Filelib\Storage\AmazonS3Storage;

class ZencoderPluginTest extends \Xi\Tests\Filelib\TestCase
{

    public function getDefaults()
    {
        return array(
            // See https://app.zencoder.com/docs/api/encoding for possible values
            "test" => true,
            "mock" => true, // Do not process, job and output IDs will be null!
            "pass_through" => "ZenCoderPluginTest",
            "region" => "europe",
        );
    }

    public function mockMp4Output($label = null)
    {
        $mp4 = array(
            "format" => "mp4",
            "video_codec" => "h264",
            "audio_codec" => "aac",
        );
        if ($label) {
            $mp4['label'] = $label;
        }
        return $mp4 + $this->getDefaults();
    }

    /**
     * @param string $url
     * @param string $label
     */
    public function mockJob(
        $label = null,
        $url = "http://composed.nu/tmp/jesse.mov"
    ) {
        return array(
            "test" => true,
            "mock" => true,
            "input" => $url,
            "output" => array(
                $this->mockMp4Output($label)
            )
        );
    }

    /**
     * @todo make a @depends annotation for AmazonS3StorageTest!
     * @todo remove dependency on Amazon S3, and enable other storage backends like sftp (must be able to give urls to zencopder API)
     * See valid input urls at: https://app.zencoder.com/docs/api/encoding/job/input
     */
    public function getAmazonS3Storage()
    {
        if (!class_exists('\\Zend_Service_Amazon_S3')) {
            $this->markTestSkipped('Zend_Service_Amazon_S3 class could not be loaded');
        }

        if (S3_KEY === 's3_key') {
            $this->markTestSkipped('S3 not configured');
        }

        $storage = new AmazonS3Storage();
        $storage->setFilelib($this->getFilelib());
        $storage->setKey(S3_KEY);
        $storage->setSecretKey(S3_SECRETKEY);
        $storage->setBucket(S3_BUCKET);

        return $storage;
    }

    public function setUp()
    {
        if (!class_exists('\\Services_Zencoder')) {
            $this->markTestSkipped('ZencoderService class could not be loaded');
        }

        if (!defined('ZENCODER_KEY')) {
            $this->markTestSkipped('Zencoder service not configured');
        }

        $this->zencoder = new ZencoderService(ZENCODER_KEY, ZENCODER_VERSION, ZENCODER_HOST);
        $this->storage = $this->getAmazonS3Storage();
        $this->defaults = $this->getDefaults();
    }

    public function tearDown()
    {
        if (!class_exists('\\Zend_Service_Amazon_S3')) {
            return;
        }

        if (S3_KEY === 's3_key') {
            $this->markTestSkipped('S3 not configured');
        }

        $this->storage->getAmazonService()->cleanBucket($this->storage->getBucket());
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Plugin\Video\ZencoderPlugin'));
        $this->assertArrayHasKey('Xi\Filelib\Plugin\AbstractPlugin', class_parents('Xi\Filelib\Plugin\Video\ZencoderPlugin'));
    }

    /**
     * @test
     */
    public function pluginShouldProvideForVideo()
    {
        $plugin = new ZencoderPlugin();
        $this->assertEquals(array('video'), $plugin->getProvidesFor());
    }

    /**
     * @test
     * @group service
     */
    public function missingInput()
    {
        try {
            $job = $this->zencoder->jobs->create(array(
                // missing "input" key on purpose
                "test" => true,
                "private" => true
            ));
        }
        catch (Services_Zencoder_Exception $e) {
            $zen_errors = $e->getErrors();
            $this->assertEquals(1, count($zen_errors));

            foreach ($zen_errors as $error) {
                $this->assertEquals($error, "Url of input file can't be blank");
            }
        }

        if (isset($job)) {
            $this->assertTrue($this->zencoder->jobs->cancel($job->id), "Job #". $job->id ." could not be cancelled!");
        }
    }

    /**
     * @test
     * @group service
     */
    public function createMockJob()
    {
        $label = 'mp4';
        $options = $this->mockJob($label);

        try {
            $job = $this->zencoder->jobs->create($options + $this->defaults);
            $output = $job->outputs[$label];

            $this->assertContains("zencoder", $output->url);
            $this->assertContains($label, $output->label);
        }
        catch (Services_Zencoder_Exception $e) {
            $this->failWithServiceErrors($e);
        }
    }

    /**
     * @test
     * @group service
     * @todo this is slow - do something differently?
     */
    public function createMinimalJob()
    {
        $label = 'mp4';
        $options = array("mock" => false) + $this->mockJob($label);

        try {
            $job = $this->zencoder->jobs->create($options + $this->defaults);
            $output = $job->outputs[$label];

            $progress = $this->zencoder->outputs->progress($output->id);
            $this->assertContains($progress->state, array("waiting", "queued", "assigning", "processing", "finished"));
        }
        catch (Services_Zencoder_Exception $e) {
            $this->failWithServiceErrors($e);
        }

        if (isset($job) && $job !== null) {
            $this->assertTrue($this->zencoder->jobs->cancel($job->id), "Job #". $job->id ." could not be cancelled!");
        }
    }

    /**
     * @test
     * @plugin
     */
    public function constructShouldInitService()
    {
        $plugin = new ZenCoderPlugin();

        $this->assertObjectHasAttribute('service', $plugin);
        $this->assertInstanceOf('\\Services_Zencoder', $plugin->getService());
    }

    /**
     * @test
     * @plugin
     */
    public function createVersionsShouldCreateVersions()
    {
        // Setup and upload file

        $name = '20th.wav';

        $original = realpath(ROOT_TESTS . '/data') . '/' . $name;
        $id = implode('/', array(uniqid('', true), $name));
        $file = FileItem::create(array('id' => $id, 'name' => $name));

        $this->storage->store($file, $original);

        // Mocks

        /* $filelib = $this->getMock('Xi\Filelib\FileLibrary'); */
        /* $filelib->expects($this->any())->method('getTempDir')->will($this->returnValue('/tmp/dir')); */

        /* $storage = $this->getMockForAbstractClass('Xi\Filelib\Storage\Storage'); */
        /* $storage->expects($this->once())->method('retrieve')->with($this->equalTo($file))->will($this->returnValue($fobject)); */

        /* $publisher = new SymlinkPublisher(); */
        /* $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Filesystem\SymlinkPublisher'); */
        /* $publisher->expects($this->any())->method('publish')->with($this->equalTo($file))->will($this->returnValue($this)); */
        /* $publisher->expects($this->any())->method('getUrl')->will($this->returnValue('//foo.com/movie.mp4')); */

        /* $filelib->expects($this->any())->method('getStorage')->will($this->returnValue($this->storage)); */

        // Test

        $plugin = new ZenCoderPlugin();
        $plugin->setFilelib($this->getFilelib());

        $ret = $plugin->createVersions($file);

        $this->assertInternalType('array', $ret);

        /* foreach ($ret as $version => $tmp) { */
        /*     $this->assertRegExp("#/tmp/dir#", $tmp); */
        /* } */

    }

    private function failWithServiceErrors($e)
    {
        $this->fail("Zencoder service responded with errors:\n" . $this->getServiceErrors($e));
    }

    private static function getServiceErrors($e)
    {
        $msgs = array();
        $errors = $e->getErrors();
        foreach ($errors as $msg) {
            $msgs += (array)$msg;
        }
        return implode("\n", $msgs);
    }

}
