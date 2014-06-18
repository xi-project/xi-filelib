<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Video;

use Guzzle\Service\Resource\Model;
use Services_Zencoder as ZencoderService;
use Services_Zencoder_Exception;
use Services_Zencoder_Job as Job;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\RuntimeException;
use Aws\S3\S3Client;
use Xi\Filelib\Plugin\VersionProvider\Version;

class ZencoderPlugin extends VersionProvider
{
    /**
     * @var ZencoderService
     */
    private $zencoderService;

    /**
     * @var string
     */
    private $tempDir;

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
     * @var S3Client
     */
    private $client;

    /**
     * @var integer
     */
    private $sleepyTime = 5;

    public function __construct(
        $apiKey,
        $awsKey,
        $awsSecretKey,
        $awsBucket,
        $outputs = array()
    ) {
        parent::__construct(
            function (File $file) {
                // @todo: maybe some more complex mime type based checking
                return (bool) preg_match("/^video/", $file->getMimetype());
            }
        );

        $this->apiKey = $apiKey;
        $this->awsKey = $awsKey;
        $this->awsSecretKey = $awsSecretKey;
        $this->awsBucket = $awsBucket;
        $this->setOutputs($outputs);
    }

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->tempDir = $filelib->getTempDir();
    }

    /**
     * @return string
     */
    public function getAwsKey()
    {
        return $this->awsKey;
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
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return S3Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = S3Client::factory(
                array(
                    'key'    => $this->awsKey,
                    'secret' => $this->awsSecretKey
                )
            );
        }
        return $this->client;
    }

    public function setClient(S3Client $client)
    {
        $this->client = $client;
    }

    public function setService(ZencoderService $service)
    {
        $this->zencoderService = $service;
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
    public function getExtension(File $file, $version)
    {
        if (preg_match("#thumbnail$#", $version)) {
            return 'png';
        }
        return $this->outputs[$version]['extension'];
    }

    /**
     * Returns an array of (potentially) provided versions
     *
     * @return array
     */
    public function getProvidedVersions()
    {
        $versions = $this->getVideoVersions();
        foreach ($versions as $version) {
            $versions[] = $version . '_thumbnail';
        }
        return $versions;
    }

    /**
     * @return array
     */
    protected function getVideoVersions()
    {
        return array_keys($this->getOutputs());
    }

    /**
     * @param  File             $file
     * @return array
     * @throws RuntimeException
     */
    public function createAllTemporaryVersions(File $file)
    {
        $retrieved = $this->storage->retrieve($file->getResource());

        /** @var Model $result */
        $result = $this->getClient()->putObject(
            array(
                'Bucket' => $this->awsBucket,
                'Key'    => $file->getUuid(),
                'SourceFile' => $retrieved,
            )
        );

        $options = array(
            "test" => false,
            "mock" => false,
            "input" => $result['ObjectURL'],
            "outputs" => $this->getOutputsToZencoder()
        );

        try {
            $job = $this->getService()->jobs->create($options);

            $this->waitUntilJobFinished($job);

            $outputs = $this->fetchOutputs($job);

            $this->getClient()->deleteObject(
                array(
                    'Bucket' => $this->awsBucket,
                    'Key' => $file->getUuid()
                )
            );

            return $outputs;

        } catch (Services_Zencoder_Exception $e) {
            throw new RuntimeException(
                "Zencoder service responded with errors: " . $this->getZencoderErrors($e),
                500,
                $e
            );
        }
    }

    /**
     * Fetch all output versions from Zencoder
     *
     * @param  Job   $job Zencoder Job
     * @return array Array of temporary filenames
     */
    protected function fetchOutputs(Job $job)
    {
        $tmps = array();
        foreach ($this->getVideoVersions() as $version) {
            $raw = $this->fetchOutput($job, $version);
            $tmps[$version] = $raw[0];
            $tmps[$version . '_thumbnail'] = $raw[1];
        }
        return $tmps;
    }

    /**
     * Fetch a single output version using Zencoder Job details
     *
     * @param  Job    $job     Zencoder Job
     * @param  string $version Version identifier
     * @return array Temporary filenames for video and its thumb
     */
    protected function fetchOutput(Job $job, $version)
    {
        $tempnam = tempnam($this->tempDir, 'zen');
        $thumbnam = tempnam($this->tempDir, 'zen');

        $output = $job->outputs[$version];
        $details = $this->getService()->outputs->details($output->id);

        file_put_contents($tempnam, file_get_contents($details->url));

        $thumb = array_shift($details->thumbnails[0]->images);
        file_put_contents($thumbnam, file_get_contents($thumb->url));

        return array(
            $tempnam,
            $thumbnam
        );
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
        return array_values(
            array_map(
                function ($output) {

                    $output['output']['thumbnails'] = array(array(
                        'label' => "{$output['output']['label']}-thumbnail",
                        "filename" => "{$output['output']['label']}-{{number}}-thumbnail",
                        'format' => 'png',
                        'start_at_first_frame' => true,
                        'number' => 1,
                    ));

                    return $output['output'];
                },
                $this->getOutputs()
            )
        );

    }

    public function isSharedResourceAllowed()
    {
        return true;
    }

    public function areSharedVersionsAllowed()
    {
        return true;
    }

    public function isValidVersion(Version $version)
    {
        return in_array(
            $version->toString(),
            $this->getProvidedVersions()
        );
    }

    private function waitUntilJobFinished(Job $job)
    {
        do {
            $done = 0;
            sleep($this->getSleepyTime());

            foreach ($this->getVideoVersions() as $label) {
                $output = $job->outputs[$label];
                $progress = $this->getService()->outputs->progress($output->id);

                if ($progress->state === 'finished') {
                    $done = $done + 1;
                }
            }

        } while ($done < count($this->getVideoVersions()));
    }

    /**
     * @param  Services_Zencoder_Exception $exception
     * @return string
     */
    private function getZencoderErrors(Services_Zencoder_Exception $exception)
    {
        $msgs = array();
        foreach ($exception->getErrors() as $error) {
            $msgs[] = (string) $error;
        }

        return implode(". ", $msgs);
    }
}
