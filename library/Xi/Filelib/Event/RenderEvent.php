<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\File\File;
use Xi\Filelib\Renderer\Response;
use Xi\Filelib\Versionable\Version;

/**
 * File event
 */
class RenderEvent extends Event
{
    /**
     * @var File
     */
    private $file;

    /**
     * @var string
     */
    private $version;

    /**
     * @var Response
     */
    private $internalResponse;

    /**
     * @var mixed
     */
    private $adaptedResponse;

    /**
     * @param File $file
     * @param string $version
     * @param Response $internalResponse
     * @param mixed $adaptedResponse
     */
    public function __construct(Response $internalResponse, $adaptedResponse, $version, File $file = null)
    {
        $this->internalResponse = $internalResponse;
        $this->adaptedResponse = $adaptedResponse;
        $this->version = Version::get($version);
        $this->file = $file;
    }

    /**
     * Returns file
     *
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return Response
     */
    public function getInternalResponse()
    {
        return $this->internalResponse;
    }

    /**
     * @param mixed $response
     */
    public function setAdaptedResponse($response)
    {
        $this->adaptedResponse = $response;
    }

    /**
     * @return mixed
     */
    public function getAdaptedResponse()
    {
        return $this->adaptedResponse;
    }
}
