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
use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\ResourceEvent;
use Xi\Filelib\Storage\Adapter\StorageAdapter;
use Xi\Filelib\Storage\Storable;
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
     * @var StorageAdapter
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
     * @var bool
     */
    protected $canBeLazy = false;

    /**
     * @param callable $isApplicableTo
     */
    public function __construct($isApplicableTo)
    {
        $this->isApplicableTo = $isApplicableTo;
    }

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->storage = $filelib->getStorage();
        $this->profiles = $filelib->getProfileManager();
    }

    public function createProvidedVersions(File $file)
    {
        $tmps = $this->createTemporaryVersions($file);

        $versionable = $this->areSharedVersionsAllowed() ? $file->getResource() : $file;
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

    abstract public function createTemporaryVersions(File $file);

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

        if (!$this->belongsToProfile($file->getProfile()) || !$this->isApplicableTo($file) || $this->areProvidedVersionsCreated($file)) {
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
            if ($this->storage->versionExists($storable, $version)) {
                $this->storage->deleteVersion($storable, $version);
            }
        }
    }

    public function areProvidedVersionsCreated(File $file)
    {
        $versionable = $this->areSharedVersionsAllowed() ? $file->getResource() : $file;

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
        $versionable = $this->areSharedVersionsAllowed() ? $file->getResource() : $file;

        $retrieved = $this->storage->retrieveVersion(
            $versionable,
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
     * @return bool
     */
    public function canBeLazy()
    {
        return $this->canBeLazy;
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
