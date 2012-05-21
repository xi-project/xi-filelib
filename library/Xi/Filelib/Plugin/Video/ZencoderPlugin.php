<?php

namespace Xi\Filelib\Plugin\Video;

use Services_Zencoder as ZencoderService;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Publisher\Filesystem\SymlinkPublisher;
use Xi\Filelib\Renderer\SymfonyRenderer;
use Xi\Filelib\Storage\AmazonS3Storage;

class ZencoderPlugin extends AbstractVersionProvider implements VersionProvider
{
    protected $service;
    protected $providesFor = array('video');

    private $key;
    private $host;
    private $version;
    private $debug;

    public function __construct($options = array())
    {
        Configurator::setOptions($this, $options);
        $this->getService();
    }

    /**
     * Returns Zencoder-php service instance
     *
     * @return ZencoderService
     */
    public function getService() {
        if (!$this->service) {
            $this->service = new ZencoderService($this->key, $this->version, $this->host, $this->debug);
        }
        return $this->service;
    }

    /**
     * Returns file extension for a version
     *
     * @param string $version
     */
    public function getExtensionFor($version) {}

    /**
     * Returns an array of (potentially) provided versions
     *
     * @return array
     */
    public function getVersions()
    {
        return array(
            'mp4',
            // 'mp4-small',
        );
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
        $storage = $this->getAmazonS3Storage();

        if (!($storage instanceof AmazonS3Storage)) {
            throw new FilelibException(sprintf('Method "%s()" not implemented for storage "%s"', __FUNCTION__, $storage));
        }

        $s3 = $storage->getAmazonService();

        $object = str_replace($storage->getBucket() .'/', '', $storage->getPath($file));
        $bucket = $storage->getBucket();

        if ($storage->getBucket() === 'zencoder-fake') {
            // Stupid Zend_Service_Amazon_S3 only allows alphanum characters in the bucket name,
            // so this is necessary for using a local fake-s3 with a subdomain bucket.
            $url = 'http://' . $bucket . '.' . $s3::S3_ENDPOINT . '/' . $object;
        }
        else {
            // Use default without subdomain buckets
            $url = implode('/', array($s3->getEndpoint(), $bucket, $object));
        }

        var_dump("S3 endpoint: ". $s3->getEndpoint());
        var_dump("Bucket: ". $bucket);
        var_dump("Object: ". $object);
        var_dump("Url: ". $url);

        //$stored = $s3->getObject($object); // <- Actual file

        /* $path = $storage->retrieve($file)->getPathname(); */
        /* var_dump("Path: ". $path); */


        // * Get orignal file from the path

        //$tmp = implode('/', array($filelib->getTempDir(), uniqid('', true), $path));
        /* var_dump($tmp); */

        /* $var_dump($this->getProfiles()); */


        // * Get S3 url of the uploaded file

        /* $storage->store($file, $path); */
        //$retrieved = $storage->retrieve($file);

        /* $url = $storage->getPath($file); */

        /* var_dump("URL: ". $url); */


        // * Create a Zencoder job with url

        $options = array(
            "test" => true,
            "mock" => false,
            "input" => "http://composed.nu/tmp/jesse.mov", /* <- Change to S3 $url */
            "output" => array(
                array(
                    "label" => "mp4",
                    "format" => "mp4",
                    "video_codec" => "h264",
                    "audio_codec" => "aac",
                )
            )
        );

        try {
            $job = $zencoder->jobs->create($options);
            $output = $job->outputs[$label];

            $progress = $zencoder->outputs->progress($output->id);
            $this->assertContains($progress->state, array("waiting", "queued", "assigning", "processing", "finished"));
        }
        catch (Services_Zencoder_Exception $e) {
            throw new FilelibException("Zencoder service responded with errors:\n" . $this->getServiceErrors($e));
        }

        // * Poll for progress (job and outputs)
        // * Download and store versions

        //$transcoded = $storage->store($file, $tmp);


        // * Ready
        return array($this->getIdentifier() => $output);

    }

    protected function getAmazonS3Storage()
    {
        $storage = new AmazonS3Storage();
        $storage->setFilelib($this->getFilelib());
        $storage->setKey(S3_KEY);
        $storage->setSecretKey(S3_SECRETKEY);
        $storage->setBucket(S3_BUCKET);

        return $storage;
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
