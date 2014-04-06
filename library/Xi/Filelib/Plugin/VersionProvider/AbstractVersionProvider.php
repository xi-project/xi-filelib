<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\VersionProvider;

use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\RuntimeException;
use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\ResourceEvent;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Events;
use Xi\Filelib\File\MimeType;
use Xi\Filelib\File\FileObject;

/**
 * Abstract convenience class for version provider plugins
 *
 * @author pekkis
 */
abstract class AbstractVersionProvider extends AbstractPlugin implements VersionProvider
{
    protected static $subscribedEvents = array(
        Events::FILE_AFTER_AFTERUPLOAD => 'onAfterUpload',
        Events::FILE_AFTER_DELETE => 'onFileDelete',
        Events::RESOURCE_AFTER_DELETE => 'onResourceDelete',
    );

    /**
     * @var string Version identifier
     */
    protected $identifier;

    /**
     * @var array Array of file types for which the plugin provides a version
     */
    protected $providesFor;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var ProfileManager
     */
    protected $profiles;

    /**
     * @var array
     */
    protected $extensionReplacements = array('jpeg' => 'jpg');

    /**
     * @param string $identifier
     * @param callable $providesFor
     */
    public function __construct($identifier, $providesFor)
    {
        $this->identifier = $identifier;
        $this->providesFor = $providesFor;
    }

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->storage = $filelib->getStorage();
        $this->profiles = $filelib->getProfileManager();
    }

    public function createVersions(File $file)
    {
        $tmps = $this->createTemporaryVersions($file);
        $versionable = $this->areSharedVersionsAllowed() ? $file->getResource() : $file;
        foreach (array_keys($tmps) as $version) {
            $versionable->addVersion($version);
        }

        foreach ($tmps as $version => $tmp) {
            $this->storage->storeVersion(
                $file->getResource(),
                $version,
                $tmp,
                $this->areSharedVersionsAllowed() ? null : $file
            );
            unlink($tmp);
        }
    }

    abstract public function createTemporaryVersions(File $file);

    /**
     * Returns identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns whether the plugin provides a version for a file.
     *
     * @param  File    $file File item
     * @return boolean
     */
    public function providesFor(File $file)
    {
        if (!$this->hasProfile($file->getProfile())) {
            return false;
        }
        return call_user_func($this->providesFor, $file);
    }

    public function onAfterUpload(FileEvent $event)
    {

        $file = $event->getFile();

        if (!$this->hasProfile($file->getProfile()) || !$this->providesFor($file) || $this->areVersionsCreated($file)) {
            return;
        }

        $this->createVersions($file);
    }

    public function onFileDelete(FileEvent $event)
    {
        $file = $event->getFile();

        if (!$this->hasProfile($file->getProfile())) {
            return;
        }

        if (!$this->providesFor($file)) {
            return;
        }
        $this->deleteFileVersions($file);
    }

    /**
     * Deletes resource versions
     *
     * @param ResourceEvent $event
     */
    public function onResourceDelete(ResourceEvent $event)
    {
        foreach ($this->getVersions() as $version) {
            if ($this->storage->versionExists($event->getResource(), $version)) {
                $this->storage->deleteVersion($event->getResource(), $version);
            }
        }
    }

    /**
     * Deletes file versions
     *
     * @param File $file
     */
    public function deleteFileVersions(File $file)
    {
        foreach ($this->getVersions() as $version) {
            if ($this->storage->versionExists($file->getResource(), $version, $file)) {
                $this->storage->deleteVersion($file->getResource(), $version, $file);
            }
        }
    }

    public function areVersionsCreated(File $file)
    {
        $versionable = $this->areSharedVersionsAllowed() ? $file->getResource() : $file;

        $count = 0;
        foreach ($this->getVersions() as $version) {
            if ($versionable->hasVersion($version)) {
                $count++;
            }
        }
        if ($count == count($this->getVersions())) {
            return true;
        }

        return false;
    }

    public function getExtensionFor(File $file, $version)
    {
        $retrieved = $this->storage->retrieveVersion(
            $file->getResource(),
            $version,
            $this->areSharedVersionsAllowed() ? null : $file
        );

        $fileObj = new FileObject($retrieved);
        $extensions = MimeType::mimeTypeToExtensions($fileObj->getMimeType());

        $ret = array_shift($extensions);
        return $this->doExtensionReplacement($ret);
    }

    /**
     * Apache parsings produce some unwanted results (jpeg). Switcheroo those
     *
     * @param string $extension
     * @return string
     * @todo allow user to edit / add his own replacements
     *
     */
    protected function doExtensionReplacement($extension)
    {
        if (isset($this->extensionReplacements[$extension])) {
            return $this->extensionReplacements[$extension];
        }
        return $extension;
    }
}
