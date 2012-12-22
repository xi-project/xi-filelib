<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Video;

use Services_Zencoder as ZencoderService;
use Services_Zencoder_Exception;
use Services_Zencoder_Job as Job;
use ZendService\Amazon\S3\S3 as AmazonService;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\File\FileOperator;

class ZencoderPlugin extends AbstractVersionProvider implements VersionProvider
{
    /**
     * @var ZencoderService
     */
    private $zencoderService;

    /**
     * @var AmazonService
     */
    private $amazonService;

    /**
     * @var string
     */
    private $tempDir;

    protected $providesFor = array('video');

    /**
     * @var array
     */
    private $outputs = array();

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $awsKey;

    /**
     * @var string
     */
    private $awsSecretKey;

    /**
     * @var string
     */
    private $awsBucket;

    /**
     * @var integer
     */
    private $sleepyTime = 5;

    public function __construct(
        Storage $storage,
        Publisher $publisher,
        FileOperator $fileOperator,
        ZencoderService $zencoderService,
        AmazonService $amazonService,
        $tempDir,
        $options = array()
    ) {
        parent::__construct($storage, $publisher, $fileOperator, $options);

        $this->zencoderService = $zencoderService;
        $this->amazonService = $amazonService;
        $this->tempDir = $tempDir;

        Configurator::setOptions($this, $options);
    }

    /**
     * @param  string         $awsKey
     * @return ZencoderPlugin
     */
    public function setAwsKey($awsKey)
    {
        $this->awsKey = $awsKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getAwsKey()
    {
        return $this->awsKey;
    }

    /**
     * @param  string         $awsSecretKey
     * @return ZencoderPlugin
     */
    public function setAwsSecretKey($awsSecretKey)
    {
        $this->awsSecretKey = $awsSecretKey;

        return $this;
    }

    /**
     * @param  string         $bucket
     * @return ZencoderPlugin
     */
    public function setAwsBucket($bucket)
    {
        $this->awsBucket = $bucket;

        return $this;
    }

    /**
     * @return string
     */
    public function getAwsBucket()
    {
        return $this->awsBucket;
    }

    /**
     * @return string
     */
    public function getAwsSecretKey()
    {
        return $this->awsSecretKey;
    }

    /**
     * @param  string         $apiKey
     * @return ZencoderPlugin
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return AmazonService
     */
    public function getAwsService()
    {
        if (!$this->amazonService) {
            $this->amazonService = new AmazonService($this->getAwsKey(), $this->getAwsSecretKey());
        }

        return $this->amazonService;
    }

    /**
     * Sets sleepy time in seconds
     *
     * @param  integer        $sleepyTime
     * @return ZencoderPlugin
     */
    public function setSleepyTime($sleepyTime)
    {
        $this->sleepyTime = $sleepyTime;
        return $this;
    }

    /**
     * @return integer
     */
    public function getSleepyTime()
    {
        return $this->sleepyTime;
    }

    /**
     * Returns Zencoder-php service instance
     *
     * @return ZencoderService
     */
    public function getService()
    {
        if (!$this->zencoderService) {
            $this->zencoderService = new ZencoderService($this->getApiKey());
        }

        return $this->zencoderService;
    }

    /**
     * Returns file extension for a version
     *
     * @param string $version
     */
    public function getExtensionFor($version)
    {
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
     * @param  File             $file
     * @return array
     * @throws FilelibException
     * @todo should not depend on Amazon S3
     */
    public function createVersions(File $file)
    {
        $s3 = $this->getAwsService();
        $awsPath = $this->getAwsBucket() . '/' . uniqid('zen');

        $retrieved = $this->getStorage()->retrieve($file->getResource());

        $s3->putFile($retrieved->getRealPath(), $awsPath);

        $url = $s3->getEndpoint() . '/' . $awsPath;

        $options = array(
            "test" => false,
            "mock" => false,
            "input" => $url,
            "outputs" => $this->getOutputsToZencoder()
        );

        try {
            $job = $this->getService()->jobs->create($options);

            $this->waitUntilJobFinished($job);

            $outputs = $this->fetchOutputs($job);

            $s3->removeObject($awsPath);

            return $outputs;

        } catch (Services_Zencoder_Exception $e) {
            throw new FilelibException(
                "Zencoder service responded with errors: " . $this->getZencoderErrors($e), 500, $e
            );
        }
    }

    /**
     * Fetch all output versions from Zencoder
     *
     * @param   Job     $job      Zencoder Job
     * @return  array             Array of temporary filenames
     */
    protected function fetchOutputs(Job $job) {
        $tmps = array();
        foreach ($this->getVersions() as $version) {
            $tmps[$version] = $this->fetchOutput($job, $version);
        }
        return $tmps;
    }

    /**
     * Fetch a single output version using Zencoder Job details
     *
     * @param   Job     $job      Zencoder Job
     * @param   string  $version  Version identifier
     * @return  string            Temporary filename
     */
    protected function fetchOutput(Job $job, $version)
    {
        $tempnam = tempnam($this->tempDir, 'zen');
        $output = $job->outputs[$version];
        $details = $this->getService()->outputs->details($output->id);

        file_put_contents($tempnam, file_get_contents($details->url));
        return $tempnam;
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

    public function isSharedResourceAllowed()
    {
        return true;
    }

    public function areSharedVersionsAllowed()
    {
        return true;
    }

    private function waitUntilJobFinished(Job $job) {
        do {
            $done = 0;
            sleep($this->getSleepyTime());

            foreach ($this->getVersions() as $label) {
                $output = $job->outputs[$label];
                $progress = $this->getService()->outputs->progress($output->id);

                if ($progress->state === 'finished') {
                    $done = $done + 1;
                }
            }

        } while ($done < count($this->getVersions()));
    }

    /**
     * @param Services_Zencoder_Exception $exception
     * @return string
     */
    private function getZencoderErrors(Services_Zencoder_Exception $exception) {
        $msgs = [];
        foreach ($exception->getErrors() as $error) {
            $msgs[] = (string) $error;
        }
        return implode(". ", $msgs);
    }
}
