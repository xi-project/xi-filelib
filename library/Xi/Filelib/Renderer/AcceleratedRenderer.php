<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Renderer;

class AcceleratedRenderer extends Renderer
{
    /**
     * Server signature regexes and their headers
     *
     * @var array
     */
    protected static $serverSignatures = array(
        '[^nginx]' => 'x-accel-redirect',
        '[^Apache]' => 'x-sendfile',
        '[^lighttpd/(1\.5|2)]' => 'x-sendfile',
        '[^lighttpd/1.4]' => 'x-lighttpd-send-file',
        '[^Cherokee]' => 'x-sendfile',
    );

    /**
     * @var string
     */
    private $accelerationHeader;

    /**
     * @var string
     */
    private $stripPrefixFromAcceleratedPath = '';

    /**
     * @var string
     */
    private $addPrefixToAcceleratedPath = '';

    /**
     * @var boolean
     */
    private $accelerationEnabled = false;

    /**
     *
     * @return boolean Returns whether response can be accelerated
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

    /**
     *
     * @param string $stripPrefix
     */
    public function setStripPrefixFromAcceleratedPath($stripPrefix)
    {
        $this->stripPrefixFromAcceleratedPath = $stripPrefix;
    }

    public function getStripPrefixFromAcceleratedPath()
    {
        return $this->stripPrefixFromAcceleratedPath;
    }

    public function setAddPrefixToAcceleratedPath($addPrefix)
    {
        $this->addPrefixToAcceleratedPath = $addPrefix;
    }

    public function getAddPrefixToAcceleratedPath()
    {
        return $this->addPrefixToAcceleratedPath;
    }

    /**
     * Sets acceleration header name
     *
     * @param string $header
     */
    protected function setAccelerationHeader($header)
    {
        $this->accelerationHeader = $header;
    }

    /**
     * Returns acceleration header name
     *
     * @return string
     */
    protected function getAccelerationHeader()
    {
        return $this->accelerationHeader;
    }

}
