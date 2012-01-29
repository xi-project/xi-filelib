<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib\FileLibrary,
    Xi\Filelib\Configurator,
    Xi\Filelib\FilelibException;

/**
 * Abstract backend implementing common methods
 *
 * @author  pekkis
 * @package Xi_Filelib
 */
abstract class AbstractBackend implements Backend
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    /**
     * @param mixed $options
     */
    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }

    /**
     * @param  FileLibrary     $filelib
     * @return AbstractBackend
     */
    public function setFilelib(FileLibrary $filelib)
    {
        $this->filelib = $filelib;

        return $this;
    }

    /**
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->filelib;
    }

    public function init()
    {}

    /**
     * @param  string           $url
     * @throws FilelibException
     */
    protected function assertValidUrl($url)
    {
        if (is_array($url) || is_object($url)) {
            throw new FilelibException('URL must be a string.');
        }
    }
}
