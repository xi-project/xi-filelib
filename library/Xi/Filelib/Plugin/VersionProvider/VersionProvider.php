<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\VersionProvider;

use Xi\Filelib\Event\VersionProviderEvent;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\Plugin\BasePlugin;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\ResourceEvent;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\Versionable;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Events as CoreEvents;
use Xi\Filelib\File\MimeType;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Version;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
        CoreEvents::FILE_AFTER_AFTERUPLOAD => 'onAfterUpload',
        CoreEvents::FILE_AFTER_DELETE => 'onFileDelete',
        CoreEvents::RESOURCE_AFTER_DELETE => 'onResourceDelete',
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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

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

    abstract protected function doCreateAllTemporaryVersions(File $file);

    public function createAllTemporaryVersions(File $file)
    {
        return $this->doCreateAllTemporaryVersions($file);
    }

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->storage = $filelib->getStorage();
        $this->profiles = $filelib->getProfileManager();
        $this->eventDispatcher = $filelib->getEventDispatcher();
    }

    public function provideAllVersions(File $file)
    {
        $versionable = $this->getApplicableVersionable($file);
        $versions = $this->createAllTemporaryVersions($file);

        foreach ($versions as $version => $tmp) {
            $version = Version::get($version);
            $this->storage->storeVersion($versionable, $version, $tmp);
            $versionable->addVersion($version);
            unlink($tmp);
        }

        $event = new VersionProviderEvent($this, $file, array_keys($versions));
        $this->eventDispatcher->dispatch(Events::VERSIONS_PROVIDED, $event);
    }

    /**
     * Returns whether the plugin provides versions for a file.
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

    abstract public function isValidVersion(Version $version);

    public function onAfterUpload(FileEvent $event)
    {
        $file = $event->getFile();

        if (!$this->belongsToProfile($file->getProfile())
            || !$this->isApplicableTo($file)
            || $this->areProvidedVersionsCreated($file)) {
            return;
        }

        $this->provideAllVersions($file);
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
    public function deleteProvidedVersions(Versionable $versionable)
    {
        foreach ($this->getProvidedVersions() as $version) {
            $version = Version::get($version);
            $versionable->removeVersion($version);
            if ($this->storage->versionExists($versionable, $version)) {
                $this->storage->deleteVersion($versionable, $version);
            }
        }
    }

    public function areProvidedVersionsCreated(File $file)
    {
        $versionable = $this->getApplicableVersionable($file);

        $count = 0;
        foreach ($this->getProvidedVersions() as $version) {
            if ($versionable->hasVersion(Version::get($version))) {
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
     * @param Version $version
     * @return string
     */
    public function getMimeType(File $file, Version $version)
    {
        $retrieved = $this->storage->retrieveVersion(
            $this->getApplicableVersionable($file),
            $version
        );

        $fileObj = new FileObject($retrieved);
        return $fileObj->getMimeType();
    }

    /**
     * Returns file extension for a version
     *
     * @param File $file
     * @param Version $version
     * @return string
     */
    public function getExtension(File $file, Version $version)
    {
        $mimeType = $this->getMimeType($file, $version);
        return $this->getExtensionFromMimeType($mimeType);
    }

    /**
     * Returns the applicable storable for this plugin
     *
     * @param File $file
     * @return Versionable
     */
    public function getApplicableVersionable(File $file)
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
