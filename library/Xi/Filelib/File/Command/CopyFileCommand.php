<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File\Command;

use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Event\FileCopyEvent;
use Xi\Filelib\InvalidArgumentException;
use DateTime;
use Xi\Filelib\Events;
use Xi\Filelib\Event\FolderEvent;

class CopyFileCommand extends AbstractFileCommand
{
    /**
     *
     * @var File
     */
    private $file;

    /**
     *
     * @var Folder
     */
    private $folder;

    public function __construct(File $file, Folder $folder)
    {
        parent::__construct();
        $this->file = $file;
        $this->folder = $folder;
    }

    /**
     * Generates name for a file copy
     *
     * @param  string                   $oldName
     * @return string
     * @throws InvalidArgumentException
     */
    public function getCopyName($oldName)
    {
        $pinfo = pathinfo($oldName);

        $match = preg_match("#(.*?)( copy( (\d+))?)?$#", $pinfo['filename'], $matches);

        if (!$match || !$oldName) {
            throw new InvalidArgumentException('Could not generate copy name');
        }

        if (sizeof($matches) == 2) {
            $ret = $matches[1] . ' copy';
        } elseif (sizeof($matches) == 3) {
            $ret = $matches[1] . ' copy 2';
        } else {
            $ret = $matches[1] . ' copy ' . ($matches[4] + 1);
        }

        if (isset($pinfo['extension'])) {
            $ret .= '.' . $pinfo['extension'];
        }

        return $ret;

    }

    /**
     * Handles impostor's resource
     *
     * @param File $file
     */
    public function handleImpostorResource(File $file)
    {
        $oldResource = $file->getResource();
        if ($oldResource->isExclusive()) {

            $retrieved = $this->fileOperator->getStorage()->retrieve($oldResource);

            $resource = new Resource();
            $resource->setDateCreated(new DateTime());
            $resource->setHash($oldResource->getHash());
            $resource->setSize($oldResource->getSize());
            $resource->setMimetype($oldResource->getMimetype());

            $this->fileOperator->getBackend()->createResource($resource);
            $this->fileOperator->getStorage()->store($resource, $retrieved);

            $file->setResource($resource);
        }

    }


    /**
     * Clones the original file and iterates the impostor's names until
     * a free one is found.
     *
     * @return File
     */
    public function getImpostor()
    {
        $impostor = clone $this->file;
        $impostor->setUuid($this->getUuid());

        foreach ($impostor->getVersions() as $version) {
            $impostor->removeVersion($version);
        }
        $this->handleImpostorResource($impostor);

        $found = $this->fileOperator->findByFilename($this->folder, $impostor->getName());

        if (!$found) {
            return $impostor;
        }

        do {
            $impostor->setName($this->getCopyName($impostor->getName()));
            $found = $this->fileOperator->findByFilename($this->folder, $impostor->getName());
        } while ($found);

        $impostor->setFolderId($this->folder->getId());

        return $impostor;
    }

    public function execute()
    {
        $impostor = $this->getImpostor($this->file);

        $event = new FileCopyEvent($this->file, $impostor);
        $this->fileOperator->getEventDispatcher()->dispatch(Events::FILE_BEFORE_COPY, $event);

        $event = new FolderEvent($this->folder);
        $this->fileOperator->getEventDispatcher()->dispatch(Events::FOLDER_BEFORE_WRITE_TO, $event);

        $this->fileOperator->getBackend()->createFile($impostor, $this->folder);

        $event = new FileCopyEvent($this->file, $impostor);
        $this->fileOperator->getEventDispatcher()->dispatch(Events::FILE_AFTER_COPY, $event);

        $command = $this->fileOperator->createCommand(
            'Xi\Filelib\File\Command\AfterUploadFileCommand',
            array($impostor)
        );

        return $command->execute();
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->file = $data['file'];
        $this->folder = $data['folder'];
        $this->uuid = $data['uuid'];
    }

    public function serialize()
    {
        return serialize(
            array(
                'file' => $this->file,
                'folder' => $this->folder,
                'uuid' => $this->uuid,
            )
        );
    }
}
