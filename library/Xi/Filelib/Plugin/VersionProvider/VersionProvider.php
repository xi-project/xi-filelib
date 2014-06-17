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
use Xi\Filelib\Plugin\BasePlugin;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\ResourceEvent;
use Xi\Filelib\Storage\Storable;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Events;
use Xi\Filelib\File\MimeType;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Storage\Storage;

/**
 * Base version provider
 *
 * @author pekkis
 */
abstract class VersionProvider extends BasePlugin
{
    /**
     * @var array
     */
    protected static $subscribedEvents = array(
        Events::FILE_AFTER_AFTERUPLOAD => 'onAfterUpload',
        Events::FILE_AFTER_DELETE => 'onFileDelete',
        Events::RESOURCE_AFTER_DELETE => 'onResourceDelete',
    );

    /**
     * @var array
     */
    protected $isApplicableTo;

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
     * @param callable $isApplicableTo
     */
    public function __construct($isApplicableTo)
    {
        $this->isApplicableTo = $isApplicableTo;
    }

    abstract public function areSharedVersionsAllowed();

    abstract public function isSharedResourceAllowed();

    abstract public function getProvidedVersions();

    abstract public function createTemporaryVersions(File $file);

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->storage = $filelib->getStorage();
        $this->profiles = $filelib->getProfileManager();
    }

    /**
     * @param File $file
     * @param null $requestedVersion
     */
    public function createProvidedVersions(File $file, $requestedVersion = null)
    {
        $tmps = $this->createTemporaryVersions($file, $requestedVersion);

        $versionable = $this->getApplicableStorable($file);
        foreach (array_keys($tmps) as $version) {
            $versionable->addVersion($version);
        }

        foreach ($tmps as $version => $tmp) {
            $this->storage->storeVersion(
                $versionable,
                $version,
                $tmp
            );
            unlink($tmp);
        }
    }



    /**
     * Returns whether the plugin provides a version for a file.
     *
     * @param  File    $file File item
     * @return boolean
     */
    public function isApplicableTo(File $file)
    {
        if (!$this->belongsToProfile($file->getProfile())) {
            return false;
        }
        return call_user_func($this->isApplicableTo, $file);
    }

    public function onAfterUpload(FileEvent $event)
    {
        $file = $event->getFile();

        if (!$this->belongsToProfile($file->getProfile())
            || !$this->isApplicableTo($file)
            || $this->areProvidedVersionsCreated($file)) {
            return;
        }

        $this->createProvidedVersions($file);
    }

    /**
     * @param FileEvent $event
     */
    public function onFileDelete(FileEvent $event)
    {
        $this->deleteProvidedVersions($event->getFile());
    }

    /**
     * @param ResourceEvent $event
     */
    public function onResourceDelete(ResourceEvent $event)
    {
        $this->deleteProvidedVersions($event->getResource());
    }

    /**
     * Deletes storable versions
     *
     * @param File $file
     */
    public function deleteProvidedVersions(Storable $storable)
    {
        foreach ($this->getProvidedVersions() as $version) {
            $storable->removeVersion($version);
            if ($this->storage->versionExists($storable, $version)) {
                $this->storage->deleteVersion($storable, $version);
            }
        }
    }

    public function areProvidedVersionsCreated(File $file)
    {
        $versionable = $this->getApplicableStorable($file);

        $count = 0;
        foreach ($this->getProvidedVersions() as $version) {
            if ($versionable->hasVersion($version)) {
                $count++;
            }
        }
        if ($count == count($this->getProvidedVersions())) {
            return true;
        }

        return false;
    }

    /**
     * Returns the mimetype of a version provided by this plugin via retrieving and inspecting.
     * More specific plugins should override this for performance.
     *
     * @param File $file
     * @param $version
     * @return string
     */
    public function getMimeType(File $file, $version)
    {
        $retrieved = $this->storage->retrieveVersion(
            $this->getApplicableStorable($file),
            $version
        );

        $fileObj = new FileObject($retrieved);
        return $fileObj->getMimeType();
    }

    /**
     * Returns file extension for a version
     *
     * @param File $file
     * @param string $version
     * @return string
     */
    public function getExtension(File $file, $version)
    {
        $mimeType = $this->getMimeType($file, $version);
        return $this->getExtensionFromMimeType($mimeType);
    }

    /**
     * Returns the applicable storable for this plugin
     *
     * @param File $file
     * @return Storable
     */
    public function getApplicableStorable(File $file)
    {
        return ($this->areSharedVersionsAllowed()) ? $file->getResource() : $file;
    }

    /**
     * @param string $mimeType
     * @return string
     */
    protected function getExtensionFromMimeType($mimeType)
    {
        $extensions = MimeType::mimeTypeToExtensions($mimeType);
        return $this->doExtensionReplacement(array_shift($extensions));
    }

    /**
     * Apache derived parsings produce unwanted results (jpeg). Switcheroo for those.
     *
     * @param string $extension
     * @return string
     * @todo allow user to edit / add his own replacements?
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
