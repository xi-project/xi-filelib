<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Adapter\Filesystem;

use Xi\Filelib\Publisher\PublisherAdapter;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\File\File;
use Xi\Filelib\Publisher\Linker;
use Xi\Filelib\InvalidArgumentException;

/**
 * Abstract filesystem publisher convenience class
 *
 */
abstract class BaseFilesystemPublisherAdapter implements PublisherAdapter
{
    /**
     * @var integer Octal representation for directory permissions
     */
    private $directoryPermission = 0700;

    /**
     * @var integer Octal representation for file permissions
     */
    private $filePermission = 0600;

    /**
     * @var string Physical public root
     */
    private $publicRoot;

    /**
     * Base url prepended to urls
     *
     * @var string
     */
    private $baseUrl = '';

    public function __construct($publicRoot, $filePermission = "600", $directoryPermission = "700", $baseUrl = '')
    {
        if (!is_writable($publicRoot)) {
            throw new InvalidArgumentException("Directory '{$publicRoot}' is not writeable");
        }

        $this->publicRoot = $publicRoot;
        $this->filePermission = octdec($filePermission);
        $this->directoryPermission = octdec($directoryPermission);
        $this->baseUrl = $baseUrl;
    }

    /**
     * Returns base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Returns public root
     *
     * @return string
     */
    public function getPublicRoot()
    {
        return $this->publicRoot;
    }

    /**
     * Returns directory permission
     *
     * @return integer
     */
    public function getDirectoryPermission()
    {
        return $this->directoryPermission;
    }

    /**
     * Returns file permission
     *
     * @return integer
     */
    public function getFilePermission()
    {
        return $this->filePermission;
    }

    /**
     * @param File $file
     * @param string $version
     * @param Linker $linker
     * @return string
     */
    public function getUrl(File $file, $version, VersionProvider $versionProvider, Linker $linker)
    {
        $url = $this->getBaseUrl() . '/';
        $url .= $linker->getLink(
            $file,
            $version,
            $versionProvider->getExtension($file, $version)
        );
        return $url;
    }
}
