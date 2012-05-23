<?php

use Services_Zencoder as ZencoderService;
use Services_Zencoder_Account as Account;
use Services_Zencoder_Exception as ZencoderException;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\Video\ZencoderPlugin;
use Xi\Filelib\Publisher\Filesystem\SymlinkPublisher;

class ZencoderPluginTest extends \Xi\Tests\Filelib\TestCase
{

    private $config;

    public function setUp()
    {
        if (!class_exists('Services_Zencoder')) {
            $this->markTestSkipped('ZencoderService class could not be loaded');
        }

        if (!class_exists('Zend_Service_Amazon_S3')) {
            $this->markTestSkipped('Zend_Service_Amazon_S3 class could not be loaded');
        }

        if (!ZENCODER_KEY) {
            $this->markTestSkipped('Zencoder service not configured');
        }

        if (!S3_KEY) {
            $this->markTestSkipped('S3 not configured');
        }


        $this->config = array(
            'apiKey' => ZENCODER_KEY,
            'awsKey' => S3_KEY,
            'awsSecretKey' => S3_SECRETKEY,
            'awsBucket' => ZENCODER_S3_BUCKET,
            'sleepyTime' => 1,
            'outputs' => array(
                'pygmi' => array(
                    'extension' => 'mp4',
                        'output' => array(
                        'label' => 'pygmi',
                        'device_profile' => 'mobile/baseline'
                    ),
                ),
                'watussi' => array(
                    'extension' => 'mp4',
                    'output' => array(
                        'label' => 'watussi',
                        'device_profile' => 'mobile/advanced'
                    ),
                )
            )
        );

        $this->plugin = new ZencoderPlugin($this->config);

    }


    public function tearDown()
    {
        if (!class_exists('Zend_Service_Amazon_S3')) {
            return;
        }

        if (!S3_KEY) {
            $this->markTestSkipped('S3 not configured');
        }

        if (!ZENCODER_KEY) {
            $this->markTestSkipped('Zencoder service not configured');
        }

        $this->plugin->getAwsService()->cleanBucket($this->plugin->getAwsBucket());

    }


    /**
     * @test
     */
    public function settersAndGettersShouldWorkAsExpected()
    {
        $plugin = new ZencoderPlugin();

        $val = 'xooxer';
        $this->assertNull($plugin->getApiKey());
        $this->assertSame($plugin, $plugin->setApiKey($val));
        $this->assertEquals($val, $plugin->getApiKey());

        $val = 'xooxer2';
        $this->assertNull($plugin->getAwsKey());
        $this->assertSame($plugin, $plugin->setAwsKey($val));
        $this->assertEquals($val, $plugin->getAwsKey());

        $val = 'xooxer3';
        $this->assertNull($plugin->getAwsSecretKey());
        $this->assertSame($plugin, $plugin->setAwsSecretKey($val));
        $this->assertEquals($val, $plugin->getAwsSecretKey());

        $val = 'xooxer4';
        $this->assertNull($plugin->getAwsBucket());
        $this->assertSame($plugin, $plugin->setAwsBucket($val));
        $this->assertEquals($val, $plugin->getAwsBucket());

        $val = 1;
        $this->assertEquals(5, $plugin->getSleepyTime());
        $this->assertSame($plugin, $plugin->setSleepyTime($val));
        $this->assertEquals($val, $plugin->getSleepyTime());


    }

    /**
     * @test
     */
    public function getServiceShouldReturnAndCacheZencoderService()
    {
        $service = $this->plugin->getService();
        $this->assertInstanceOf('Services_Zencoder', $service);
        $this->assertSame($service, $this->plugin->getService());
    }


    /**
     * @test
     */
    public function getAwsServiceShouldReturnAndCacheAwsService()
    {
        $service = $this->plugin->getAwsService();
        $this->assertInstanceOf('Zend_Service_Amazon_S3', $service);
        $this->assertSame($service, $this->plugin->getAwsService());
    }

    /**
     * @test
     */
    public function getExtensionForShouldDigOutputsForTheCorrectExtension()
    {
        $outputs = array(
            'pygmi' => array(
                'extension' => 'lussen',
                    'output' => array(
                    'label' => 'pygmi',
                    'device_profile' => 'mobile/baseline'
                ),
            ),
            'watussi' => array(
                'extension' => 'dorfer',
                'output' => array(
                    'label' => 'watussi',
                    'device_profile' => 'mobile/advanced'
                ),
            )
        );

        $this->plugin->setOutputs($outputs);

        $this->assertEquals('lussen', $this->plugin->getExtensionFor('pygmi'));
        $this->assertEquals('dorfer', $this->plugin->getExtensionFor('watussi'));

    }

    /**
     * @test
     */
    public function getVersionsShouldReturnCorrectVersions()
    {
        $this->assertEquals(array('pygmi', 'watussi'), $this->plugin->getVersions());
    }

    /**
     * @test
     */
    public function getOutputsToZencoderShouldReturnCorrectData()
    {
        $ret = $this->plugin->getOutputsToZencoder();
        $this->assertInternalType('array', $ret);
        $this->assertCount(sizeof($this->config['outputs']), $ret);

        foreach ($ret as $rut) {
            $this->assertArrayHasKey('label', $rut);
        }


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
     * @plugin
     * @group watussi
     */
    public function createVersionsShouldCreateVersions()
    {
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\Video\ZencoderPlugin')
                       ->setConstructorArgs(array($this->config))
                       ->setMethods(array('getService', 'getAwsService'))
                       ->getMock();

        $zen = $this->getMockedZencoderService();

        $aws = $this->getMockedAwsService();

        $aws->expects($this->at(0))->method('putFile')
            ->with($this->isType('string'), $this->isType('string'));

        $aws->expects($this->at(1))->method('getEndpoint')
            ->will($this->returnValue('http://dr-kobros.com'));

        $aws->expects($this->at(2))->method('removeObject')
            ->with($this->isType('string'));

        $plugin->expects($this->any())->method('getService')
               ->will($this->returnValue($zen));


        $plugin->expects($this->any())->method('getAwsService')
               ->will($this->returnValue($aws));


        $file = File::create(array('id' => 1, 'name' => 'hauska-joonas.mp4', 'resource' => Resource::create(array('id' => 1))));

        $filelib = $this->getFilelib();

        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('retrieve')
                ->with($this->isInstanceOf('Xi\Filelib\File\Resource'))
                ->will($this->returnValue(new FileObject(ROOT_TESTS . '/data/hauska-joonas.mp4')));

        $filelib->setStorage($storage);

        $plugin->setFilelib($filelib);

        $ret = $plugin->createVersions($file);

        $this->assertInternalType('array', $ret);
        $this->assertCount(2, $ret);
        $this->assertArrayHasKey('pygmi', $ret);
        $this->assertArrayHasKey('watussi', $ret);

    }


    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function createVersionsShouldThrowExecptionOnZencoderError()
    {
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\Video\ZencoderPlugin')
                       ->setConstructorArgs(array($this->config))
                       ->setMethods(array('getService', 'getAwsService'))
                       ->getMock();

        $zen = $this->getMockedZencoderService(true);

        $aws = $this->getMockedAwsService();

        $aws->expects($this->at(0))->method('putFile')
            ->with($this->isType('string'), $this->isType('string'));


        $plugin->expects($this->any())->method('getService')
               ->will($this->returnValue($zen));
        
        $plugin->expects($this->any())->method('getAwsService')
               ->will($this->returnValue($aws));

        $file = File::create(array('id' => 1, 'name' => 'hauska-joonas.mp4', 'resource' => Resource::create(array('id' => 1))));

        $filelib = $this->getFilelib();

        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('retrieve')
                ->with($this->isInstanceOf('Xi\Filelib\File\Resource'))
                ->will($this->returnValue(new FileObject(ROOT_TESTS . '/data/hauska-joonas.mp4')));

        $filelib->setStorage($storage);
        $plugin->setFilelib($filelib);

        $ret = $plugin->createVersions($file);
    }


    public function getMockedZencoderService($makeItThrowUp = false)
    {
        $zen = $this->getMockBuilder('Services_Zencoder')
                    ->disableOriginalConstructor()
                    ->getMock();

        $zen->jobs = $this->getMockBuilder('Services_Zencoder_Jobs')
                          ->disableOriginalConstructor()
                          ->getMock();

        $zen->outputs = $this->getMockBuilder('Services_Zencoder_Outputs')
                             ->disableOriginalConstructor()
                             ->getMock();

        $job = $this->getMockBuilder('Services_Zencoder_Job')
                          ->disableOriginalConstructor()
                          ->getMock();

        if ($makeItThrowUp) {
            $zen->jobs->expects($this->once())->method('create')
                ->will($this->throwException(new \Services_Zencoder_Exception('I threw up')));
            return $zen;
        }

        $zen->jobs->expects($this->once())->method('create')
                ->will($this->returnValue($job));

        $job->outputs['watussi'] = $this->getMockBuilder('Services_Zencoder_Output')
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $job->outputs['watussi']->id = 1;

        $job->outputs['pygmi'] = $this->getMockBuilder('Services_Zencoder_Output')
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $job->outputs['pygmi']->id = 2;

        $progressFinished = $this->getMockBuilder('Services_Zencoder_Progress')
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $progressFinished->state = 'finished';

        $progressUnfinished = $this->getMockBuilder('Services_Zencoder_Progress')
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $progressUnfinished->state = 'waiting';


        $zen->outputs->expects($this->at(0))->method('progress')
                  ->with($this->equalTo(2))
                  ->will($this->returnValue($progressUnfinished));

        $zen->outputs->expects($this->at(1))->method('progress')
                  ->with($this->equalTo(1))
                  ->will($this->returnValue($progressUnfinished));

        $zen->outputs->expects($this->at(2))->method('progress')
                  ->with($this->equalTo(2))
                  ->will($this->returnValue($progressFinished));

        $zen->outputs->expects($this->at(3))->method('progress')
                  ->with($this->equalTo(1))
                  ->will($this->returnValue($progressFinished));

        $details = $this->getMockBuilder('Services_Zencoder_Output')
                        ->disableOriginalConstructor()
                        ->getMock();
        $details->url = ROOT_TESTS . '/data/hauska-joonas.mp4';

        $zen->outputs->expects($this->at(4))
            ->method('details')
            ->with($this->equalTo(2))
            ->will($this->returnValue($details));

        $zen->outputs->expects($this->at(5))
            ->method('details')
            ->with($this->equalTo(1))
            ->will($this->returnValue($details));

        return $zen;
    }


    public function getMockedAwsService()
    {
        $aws = $this->getMockBuilder('Zend_Service_Amazon_S3')
                    ->disableOriginalConstructor()
                    ->getMock();

        return $aws;
    }




}
