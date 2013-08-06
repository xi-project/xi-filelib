<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Services_Zencoder as ZencoderService;
use Services_Zencoder_Exception as ZencoderException;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Plugin\Video\ZencoderPlugin;
use Xi\Filelib\Events;

/**
 * @group plugin
 */
class ZencoderPluginTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var ZencoderPlugin
     */
    private $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $zencoderService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $amazonService;

    public function setUp()
    {
        if (!class_exists('Services_Zencoder')) {
            $this->markTestSkipped('ZencoderService class could not be loaded');
        }

        if (!class_exists('ZendService\Amazon\S3\S3')) {
            $this->markTestSkipped('ZendService\Amazon\S3\S3 class could not be loaded');
        }

        $this->config = array(
            'apiKey' => 'api key',
            'awsKey' => 'aws key',
            'awsSecretKey' => 'aws secret key',
            'awsBucket' => 'aws bucket',
            'sleepyTime' => 5,
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

        $this->storage = $this->getMock('Xi\Filelib\Storage\Storage');

        $this->amazonService = $this->getMockedAwsService();

        $this->zencoderService = $this->getMockBuilder('Services_Zencoder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->zencoderService->jobs = $this->getMockBuilder('Services_Zencoder_Jobs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new ZencoderPlugin(
            'xooxer',
            'api key',
            'aws key',
            'aws secret key',
            'aws bucket',
            $this->config['outputs']
        );


        $fiop = $this->getMockedFileOperator(array('default'));
        $filelib = $this->getMockedFilelib(null, $fiop, null, $this->storage);
        $this->plugin->attachTo($filelib);
    }

    public function tearDown()
    {
        if (!class_exists('Zend\Service\Amazon\S3\S3')) {
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
        $this->assertEquals('api key', $this->plugin->getApiKey());

        $this->assertEquals('aws key', $this->plugin->getAwsKey());

        $this->assertEquals('aws secret key', $this->plugin->getAwsSecretKey());

        $this->assertEquals('aws bucket', $this->plugin->getAwsBucket());

        $val = 1;
        $this->assertEquals(5, $this->plugin->getSleepyTime());
        $this->assertSame($this->plugin, $this->plugin->setSleepyTime($val));
        $this->assertEquals($val, $this->plugin->getSleepyTime());
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
        $this->assertInstanceOf('ZendService\Amazon\S3\S3', $service);
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

        $file = $this->getMockedFile();

        $this->assertEquals('lussen', $this->plugin->getExtensionFor($file, 'pygmi'));
        $this->assertEquals('dorfer', $this->plugin->getExtensionFor($file, 'watussi'));
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
    public function createVersionsShouldCreateVersions()
    {
        $this->setupStubsForZencoderService();
        $this->plugin->setAwsService($this->amazonService);
        $this->plugin->setService($this->zencoderService);

        $this->amazonService
            ->expects($this->at(0))
            ->method('putFile')
            ->with($this->isType('string'), $this->isType('string'));

        $this->amazonService
            ->expects($this->at(1))
            ->method('getEndpoint')
            ->will($this->returnValue('http://dr-kobros.com'));

        $this->amazonService
            ->expects($this->at(2))
            ->method('removeObject')
            ->with($this->isType('string'));

        $file = File::create(array('id' => 1, 'name' => 'hauska-joonas.mp4', 'resource' => Resource::create(array('id' => 1))));

        $this->storage
            ->expects($this->once())
            ->method('retrieve')
            ->with($this->isInstanceOf('Xi\Filelib\File\Resource'))
            ->will($this->returnValue(ROOT_TESTS . '/data/hauska-joonas.mp4'));

        $this->plugin->setSleepyTime(0);

        $ret = $this->plugin->createTemporaryVersions($file);

        $this->assertInternalType('array', $ret);
        $this->assertCount(2, $ret);
        $this->assertArrayHasKey('pygmi', $ret);
        $this->assertArrayHasKey('watussi', $ret);
    }

    private function setupStubsForZencoderService()
    {
        $this->zencoderService->outputs = $this->getMockBuilder('Services_Zencoder_Outputs')
            ->disableOriginalConstructor()
            ->getMock();

        $job = $this->getMockBuilder('Services_Zencoder_Job')
            ->disableOriginalConstructor()
            ->getMock();

        $this->zencoderService->jobs->expects($this->once())->method('create')
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

        $this->zencoderService->outputs->expects($this->at(0))->method('progress')
            ->with($this->equalTo(2))
            ->will($this->returnValue($progressUnfinished));

        $this->zencoderService->outputs->expects($this->at(1))->method('progress')
            ->with($this->equalTo(1))
            ->will($this->returnValue($progressUnfinished));

        $this->zencoderService->outputs->expects($this->at(2))->method('progress')
            ->with($this->equalTo(2))
            ->will($this->returnValue($progressFinished));

        $this->zencoderService->outputs->expects($this->at(3))->method('progress')
            ->with($this->equalTo(1))
            ->will($this->returnValue($progressFinished));

        $details = $this->getMockBuilder('Services_Zencoder_Output')
            ->disableOriginalConstructor()
            ->getMock();
        $details->url = ROOT_TESTS . '/data/hauska-joonas.mp4';

        $this->zencoderService->outputs->expects($this->at(4))
            ->method('details')
            ->with($this->equalTo(2))
            ->will($this->returnValue($details));

        $this->zencoderService->outputs->expects($this->at(5))
            ->method('details')
            ->with($this->equalTo(1))
            ->will($this->returnValue($details));
    }

    /**
     * @test
     */
    public function createVersionsShouldThrowExceptionOnZencoderError()
    {
        $this->plugin->setAwsService($this->amazonService);
        $this->plugin->setService($this->zencoderService);

        $this->zencoderService->jobs->expects($this->once())->method('create')
            ->will($this->throwException(
                new ZencoderException(
                    'I threw up',
                    json_encode(array('errors' => array('Url of input file is invalid', 'lus')))
                )
            ));

        $this->amazonService
            ->expects($this->at(0))
            ->method('putFile')
            ->with($this->isType('string'), $this->isType('string'));

        $file = File::create(array('id' => 1, 'name' => 'hauska-joonas.mp4', 'resource' => Resource::create(array('id' => 1))));

        $this->storage
            ->expects($this->once())
            ->method('retrieve')
            ->with($this->isInstanceOf('Xi\Filelib\File\Resource'))
            ->will($this->returnValue(ROOT_TESTS . '/data/hauska-joonas.mp4'));

        $this->setExpectedException(
            'Xi\Filelib\FilelibException',
            'Zencoder service responded with errors: Url of input file is invalid. lus',
            500
        );

        $this->plugin->createTemporaryVersions($file);
    }

    private function getMockedAwsService()
    {
        return $this->getMockBuilder('ZendService\Amazon\S3\S3')
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * @test
     */
    public function pluginShouldAllowSharedResource()
    {
        $this->assertTrue($this->plugin->isSharedResourceAllowed());
    }

    /**
     * @test
     */
    public function pluginShouldAllowSharedVersions()
    {
        $this->assertTrue($this->plugin->areSharedVersionsAllowed());
    }

    /**
     * @test
     */
    public function getSubscribedEventsShouldReturnCorrectEvents()
    {
        $events = ZencoderPlugin::getSubscribedEvents();
        $this->assertArrayHasKey(Events::PROFILE_AFTER_ADD, $events);
        $this->assertArrayHasKey(Events::FILE_AFTER_AFTERUPLOAD, $events);
        $this->assertArrayHasKey(Events::FILE_AFTER_DELETE, $events);
        $this->assertArrayHasKey(Events::RESOURCE_AFTER_DELETE, $events);
    }
}
