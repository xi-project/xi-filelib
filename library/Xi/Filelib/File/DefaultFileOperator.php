<?php

namespace Xi\Filelib\File;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\AbstractOperator;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Plugin\Plugin;
use InvalidArgumentException;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Acl\Acl;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;

/**
 * File operator
 * 
 * @author pekkis
 *
 */
class DefaultFileOperator extends AbstractOperator implements FileOperator
{

    /**
     * @var array Profiles
     */
    private $profiles = array();

    /**
     * @var string Fileitem class
     */
    private $className = 'Xi\Filelib\File\FileItem';

    /**
     * Sets fileitem class
     *
     * @param string $className Class name
     * @return DefaultFileOperator
     */
    public function setClass($className)
    {
        $this->className = $className;
        return $this;
    }

    /**
     * Returns fileitem class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->className;
    }

    /**
     * Returns an instance of the currently set fileitem class
     * 
     * @param mixed $data Data as array or a file instance
     */
    public function getInstance($data = array())
    {
        $className = $this->getClass();
        $file = new $className();
        $file->setFilelib($this->getFilelib());
        if ($data) {
            $file->fromArray($data);
        }
        return $file;
    }

    /**
     * Adds a file profile
     * 
     * @param FileProfile $profile
     * @return FileLibrary
     * @throws InvalidArgumentException
     */
    public function addProfile(FileProfile $profile)
    {
        $identifier = $profile->getIdentifier();
        if (isset($this->profiles[$identifier])) {
            throw new InvalidArgumentException("Profile '{$identifier}' already exists");
        }
        $this->profiles[$identifier] = $profile;
        $profile->setFilelib($this->getFilelib());
        $profile->getLinker()->setFilelib($this->getFilelib());
        
        $this->getEventDispatcher()->addSubscriber($profile);

        return $this;
    }

    /**
     * Returns a file profile
     * 
     * @param string $identifier File profile identifier
     * @throws InvalidArgumentException
     * @return FileProfile
     */
    public function getProfile($identifier)
    {
        if (!isset($this->profiles[$identifier])) {
            throw new InvalidArgumentException("File profile '{$identifier}' not found");
        }

        return $this->profiles[$identifier];
    }

    /**
     * Returns all file profiles
     * 
     * @return array Array of file profiles
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    /**
     * Updates a file
     *
     * @param File $file
     * @return DefaultFileOperator
     */
    public function update(File $file)
    {
        $this->unpublish($file);

        $linker = $this->getProfile($file->getProfile())->getLinker();

        $file->setLink($linker->getLink($file, true));

        $this->getBackend()->updateFile($file);

        if ($this->isReadableByAnonymous($file)) {
            $this->publish($file);
        }

        return $this;
    }

    /**
     * Finds a file
     *
     * @param mixed $id File id
     * @return \Xi\Filelib\File\File
     */
    public function find($id)
    {
        $file = $this->getBackend()->findFile($id);

        if (!$file) {
            return false;
        }

        $file = $this->getInstance($file);
        return $file;
    }

    public function findByFilename(\Xi\Filelib\Folder\Folder $folder, $filename)
    {
        $file = $this->getBackend()->findFileByFilename($folder, $filename);

        if (!$file) {
            return false;
        }

        $file = $this->getInstance($file);

        return $file;
    }

    /**
     * Finds and returns all files
     *
     * @return \ArrayIterator
     */
    public function findAll()
    {
        $ritems = $this->getBackend()->findAllFiles();

        $items = array();
        foreach ($ritems as $ritem) {
            $item = $this->getInstance($ritem);
            $items[] = $item;
        }
        return $items;
    }

    /**
     * Returns whether a file is anonymous
     *
     * @param File $file File
     * @return boolean
     */
    public function isReadableByAnonymous(File $file)
    {
        return $this->getAcl()->isReadableByAnonymous($file);
    }

    /**
     * Gets a new upload
     *
     * @param string $path Path to upload file
     * @return FileUpload
     */
    public function prepareUpload($path)
    {
        $upload = new FileUpload($path);
        return $upload;
    }

    /**
     * Uploads file to filelib.
     *
     * @param mixed $upload Uploadable, path or object
     * @param Folder $folder
     * @return File
     * @throws \Xi\Filelib\FilelibException
     * @todo Remove the upload kludgeration with prepareUpload
     */
    public function upload($upload, Folder $folder, $profile = 'default')
    {
        if (!$upload instanceof FileUpload) {
            $upload = $this->prepareUpload($upload);
        }

        if (!$this->getAcl()->isWriteable($folder)) {
            throw new FilelibException("Folder '{$folder->getId()}'not writeable");
        }

        $profileObj = $this->getProfile($profile);
        foreach ($profileObj->getPlugins() as $plugin) {
            $upload = $plugin->beforeUpload($upload);
        }

        $file = $this->getInstance(array(
            'folder_id' => $folder->getId(),
            'mimetype' => $upload->getMimeType(),
            'size' => $upload->getSize(),
            'name' => $upload->getUploadFilename(),
            'profile' => $profile,
            'date_uploaded' => $upload->getDateUploaded(),
        ));

        $this->getBackend()->upload($file, $folder);

        $file->setLink($profileObj->getLinker()->getLink($file, true));

        $this->getBackend()->updateFile($file);

        $this->getStorage()->store($file, $upload->getRealPath());

        foreach ($profileObj->getPlugins() as $plugin) {
            $plugin->afterUpload($file);
        }

        if ($this->getAcl()->isReadableByAnonymous($file)) {
            $this->publish($file);
        }

        return $file;
    }

    /**
     * Deletes a file
     *
     * @param File $file
     */
    public function delete(\Xi\Filelib\File\File $file)
    {
        $this->unpublish($file);

        $this->getBackend()->deleteFile($file);

        $this->getStorage()->delete($file);

        // @todo: refactor dem goddamn plugins to Symfony events
        foreach ($this->getProfile($file->getProfile())->getPlugins() as $plugin) {
            $plugin->onDelete($file);
        }

        return true;
    }

    /**
     * Returns file type of a file
     *
     * @param \Xi\Filelib\File\File File $file item
     * @return string File type
     */
    public function getType(\Xi\Filelib\File\File $file)
    {
        // @todo Semi-mock until mimetype database is pooped in.
        $split = explode('/', $file->getMimetype());
        return $split[0];
    }

    /**
     * Returns whether a file has a certain version
     *
     * @param File $file File item
     * @param string $version Version
     * @return boolean
     */
    public function hasVersion(File $file, $version)
    {
        $profile = $this->getProfile($file->getProfile());
        return $profile->fileHasVersion($file, $version);
    }

    /**
     * Returns version provider for a file/version
     *
     * @param File $file File item
     * @param string $version Version
     * @return VersionProvider Provider
     */
    public function getVersionProvider(File $file, $version)
    {
        $profile = $this->getProfile($file->getProfile());
        return $profile->getVersionProvider($file, $version);
    }

    public function publish(\Xi\Filelib\File\File $file)
    {
        $profile = $this->getProfile($file->getProfile());
        if ($profile->getPublishOriginal()) {
            $this->getPublisher()->publish($file);
        }

        foreach ($profile->getPlugins() as $plugin) {
            $plugin->onPublish($file);
        }
    }

    public function unpublish(\Xi\Filelib\File\File $file)
    {
        $profile = $this->getProfile($file->getProfile());

        $this->getFilelib()->getPublisher()->unpublish($file);

        foreach ($profile->getPlugins() as $plugin) {
            $plugin->onUnpublish($file);
        }
    }

    public function addPlugin(Plugin $plugin, $priority = 0)
    {
        foreach ($plugin->getProfiles() as $profileIdentifier) {
            $profile = $this->getProfile($profileIdentifier);
            $profile->addPlugin($plugin, $priority);
        }
    }

}