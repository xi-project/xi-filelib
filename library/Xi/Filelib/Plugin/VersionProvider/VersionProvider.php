<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\VersionProvider;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\ResourceEvent;
use Xi\Filelib\Event\VersionProviderEvent;
use Xi\Filelib\Events as CoreEvents;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\File\FileRepository;
use Pekkis\MimeTypes\MimeTypes;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Versionable\InvalidVersionException;
use Xi\Filelib\Plugin\BasePlugin;
use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\Resource\ResourceRepository;
use Xi\Filelib\Resource\ResourceRepositoryInterface;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Versionable\Versionable;

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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var FileRepository
     */
    protected $fileRepository;

    /**
     * @var ResourceRepositoryInterface
     */
    protected $resourceRepository;

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
        $this->fileRepository = $filelib->getFileRepository();
        $this->resourceRepository = $filelib->getResourceRepository();
    }

    public function provideAllVersions(File $file)
    {
        $versions = $this->createAllTemporaryVersions($file);

        foreach ($versions as $version => $tmp) {

            $version = Version::get($version);

            $resource = $this->resourceRepository->findOrCreateResourceForPath($tmp);
            $file->addVersion($version, $resource);

            unlink($tmp);
        }

        $this->fileRepository->update($file);

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

    /**
     * @param Version $version
     * @return Version
     * @throws InvalidVersionException
     */
    public function ensureValidVersion(Version $version)
    {
        if (!in_array(
            $version->getVersion(),
            $this->getProvidedVersions()
        )) {
            throw new InvalidVersionException(
                sprintf(
                    "Invalid base version string '%s'",
                    $version->getVersion()
                )
            );
        }
        return $version;
    }

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
        $versions = $this->getProvidedVersions();

        foreach ($versions as $version) {
            $version = Version::get($version);

            $resource = $versionable->getVersion($version)->getResource();

            if ($this->storage->exists($resource)) {
                $this->storage->delete($resource);
            }
            $versionable->removeVersion($version);
        }

        $event = new VersionProviderEvent($this, $versionable, $versions);
        $this->eventDispatcher->dispatch(Events::VERSIONS_UNPROVIDED, $event);
    }

    public function areProvidedVersionsCreated(File $file)
    {
        $count = 0;
        foreach ($this->getProvidedVersions() as $version) {
            if ($file->hasVersion(Version::get($version))) {
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
        $retrieved = $this->storage->retrieve(
            $file->getVersion($version)->getResource()
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
     * @param string $mimeType
     * @return string
     */
    protected function getExtensionFromMimeType($mimeType)
    {
        $mimeTypes = new MimeTypes();
        return $mimeTypes->mimeTypeToExtension($mimeType);
    }
}
