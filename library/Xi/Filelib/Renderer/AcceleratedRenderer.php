<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Renderer;

use Xi\Filelib\File\FileObject;
use Xi\Filelib\Renderer\Adapter\AcceleratedRendererAdapter;

class AcceleratedRenderer extends Renderer
{
    /**
     * @var AcceleratedRendererAdapter
     */
    protected $adapter;

    /**
     * Server signature regexes and their headers
     *
     * @var array
     */
    protected $serverSignatures = array(
        '[^nginx]' => 'x-accel-redirect',
        '[^Apache]' => 'x-sendfile',
        '[^lighttpd/(1\.5|2)]' => 'x-sendfile',
        '[^lighttpd/1.4]' => 'x-lighttpd-send-file',
        '[^Cherokee]' => 'x-sendfile',
    );

    /**
     * @var string
     */
    private $stripPrefixFromPath = '';

    /**
     * @var string
     */
    private $addPrefixToPath = '';

    /**
     * @var boolean
     */
    private $accelerationEnabled = false;

    private $header;

    public function __construct(
        AcceleratedRendererAdapter $adapter,
        $stripPrefixFromPath = '',
        $addPrefixToPath = ''
    ) {
        parent::__construct($adapter);
        $this->stripPrefixFromPath = $stripPrefixFromPath;
        $this->addPrefixToPath = $addPrefixToPath;
    }

        /**
     *
     * @return boolean Returns whether response can be
     */
    public function isAccelerationEnabled()
    {
        return $this->accelerationEnabled;
    }

    /**
     * Enables or disables acceleration
     *
     * @param boolean $flag
     */
    public function enableAcceleration($flag)
    {
        $this->accelerationEnabled = $flag;
    }

    public function stripPrefixFromPath($stripPrefix)
    {
        $this->stripPrefixFromPath = $stripPrefix;
    }

    public function addPrefixToPath($addPrefix)
    {
        $this->addPrefixToPath = $addPrefix;
    }


    public function canAccelerate()
    {
        if (!$this->isAccelerationEnabled() || !$this->adapter->canAccelerate()) {
            return false;
        }

        $serverSignature = $this->adapter->getServerSignature();

        foreach ($this->serverSignatures as $signature => $header) {
            if (preg_match($signature, $serverSignature)) {
                $this->header = $header;
                return true;
            }
        }

        return false;
    }


    protected function injectContentToResponse(FileObject $file, Response $response)
    {
        if (!$this->canAccelerate()) {
            parent::injectContentToResponse($file, $response);
            return;
        }

        $path = preg_replace("[^{$this->stripPrefixFromPath}]", '', $file->getRealPath());
        $path = $this->addPrefixToPath . $path;

        $response->setHeader($this->header, $path);
    }
}
