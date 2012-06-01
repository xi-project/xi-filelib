<?php

namespace Xi\Filelib\Plugin\Video;

use Services_Zencoder as ZencoderService;
use Zend\Service\Amazon\S3\S3 as AmazonService;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Publisher\Filesystem\SymlinkPublisher;

class ZencoderPlugin extends AbstractVersionProvider implements VersionProvider
{
    /**
     *
     * @var ZencoderService
     */
    protected $service;

    /**
     *
     * @var AmazonService
     */
    private $awsService;

    protected $providesFor = array('video');

    /**
     *
     * @var array
     */
    private $outputs = array();

    /**
     *
     * @var string
     */
    private $apiKey;

    /**
     *
     * @var string
     */
    private $awsKey;

    /**
     *
     * @var string
     */
    private $awsSecretKey;

    /**
     *
     * @var string
     */
    private $awsBucket;

    public function __construct($options = array())
    {
        Configurator::setOptions($this, $options);
    }

    /**
     *
     * @param string $awsKey
     * @return ZencoderPlugin
     */
    public function setAwsKey($awsKey)
    {
        $this->awsKey = $awsKey;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getAwsKey()
    {
        return $this->awsKey;
    }

    /**
     *
     * @param string $awsSecretKey
     * @return ZencoderPlugin
     */
    public function setAwsSecretKey($awsSecretKey)
    {
        $this->awsSecretKey = $awsSecretKey;
        return $this;
    }

    /**
     *
     * @param string $bucket
     * @return ZencoderPlugin
     */
    public function setAwsBucket($bucket)
    {
        $this->awsBucket = $bucket;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getAwsBucket()
    {
        return $this->awsBucket;
    }

    /**
     *
     * @return string
     */
    public function getAwsSecretKey()
    {
        return $this->awsSecretKey;
    }

    /**
     *
     * @param string $apiKey
     * @return ZencoderPlugin
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     *
     * @return AmazonService
     */
    public function getAwsService()
    {
        if(!$this->awsService) {
            $this->awsService = new AmazonService($this->getAwsKey(), $this->getAwsSecretKey());
        }

        if(!$this->awsService->isBucketAvailable($this->getAwsBucket())) {
            $this->awsService->createBucket($this->getAwsBucket());
        }

        return $this->awsService;
    }



    /**
     * Returns Zencoder-php service instance
     *
     * @return ZencoderService
     */
    public function getService()
    {
        if (!$this->service) {
            $this->service = new ZencoderService($this->getApiKey());
        }
        return $this->service;
    }

    /**
     * Returns file extension for a version
     *
     * @param string $version
     */
    public function getExtensionFor($version) {

        return $this->outputs[$version]['extension'];
    }

    /**
     * Returns an array of (potentially) provided versions
     *
     * @return array
     */
    public function getVersions()
    {
        return array_keys($this->getOutputs());
    }

    /**
     * @param File $file
     * @return array
     * @todo should not depend on Amazon S3
     */
    public function createVersions(File $file)
    {
        $filelib = $this->getFilelib();

        $zencoder = $this->getService();

        $retrieved = $this->getStorage()->retrieve($file);

        $awsPath = $this->getAwsBucket() . '/' . uniqid('zen');

        $this->getAwsService()->putFile($retrieved->getRealPath(), $awsPath);

        $url = $this->getAwsService()->getEndpoint() . '/' . $awsPath;

        $options = array(
            "test" => false,
            "mock" => false,
            "input" => $url,
            "outputs" => $this->getOutputsToZencoder()
        );

        try {
            $job = $zencoder->jobs->create($options);

            do {
                $done = 0;
                sleep(5);

                foreach ($this->getVersions() as $label) {
                    $output = $job->outputs[$label];

                    $progress = $zencoder->outputs->progress($output->id);

                    if ($progress->state === 'finished') {
                        $done = $done + 1;
                    }

                }

            } while ($done < count($this->getVersions()));

            $tmps = array();

            foreach ($this->getVersions() as $version) {

                $tempnam = tempnam($this->getFilelib()->getTempDir(), 'zen');
                $output = $job->outputs[$version];
                $details = $zencoder->outputs->details($output->id);

                file_put_contents($tempnam, file_get_contents($details->url));

                $tmps[$version] = $tempnam;
            }

            $this->getAwsService()->removeObject($awsPath);

            return $tmps;

        } catch (\Services_Zencoder_Exception $e) {
            throw new FilelibException("Zencoder service responded with errors", 500, $e);
        }

    }


    public function setOutputs($outputs)
    {
        $this->outputs = $outputs;
    }

    public function getOutputs()
    {
        return $this->outputs;
    }

    public function getOutputsToZencoder()
    {
        return array_values(array_map(function($output) {
            return $output['output'];
        }, $this->getOutputs()));
    }

}
