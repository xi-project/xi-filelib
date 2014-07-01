<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File\Command;

use DateTime;
use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\Event\FileCopyEvent;
use Xi\Filelib\Event\FolderEvent;
use Xi\Filelib\Events;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\Queue\UuidReceiver;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Resource\ResourceRepository;

class CopyFileCommand extends BaseFileCommand implements UuidReceiver
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

    /**
     * @var string
     */
    private $uuid = null;

    public function __construct(File $file, Folder $folder)
    {
        $this->file = $file;
        $this->folder = $folder;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid ?: Uuid::uuid4()->toString();
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

            $retrieved = $this->storage->retrieve($oldResource);

            $resource = Resource::create();
            $resource->setDateCreated(new DateTime());
            $resource->setHash($oldResource->getHash());
            $resource->setSize($oldResource->getSize());
            $resource->setMimetype($oldResource->getMimetype());

            $this->resourceRepository->createCommand(
                ResourceRepository::COMMAND_CREATE,
                array($resource)
            )->execute();

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

        $found = $this->fileRepository->findByFilename($this->folder, $impostor->getName());

        if (!$found) {
            return $impostor;
        }

        do {
            $impostor->setName($this->getCopyName($impostor->getName()));
            $found = $this->fileRepository->findByFilename($this->folder, $impostor->getName());
        } while ($found);

        $impostor->setFolderId($this->folder->getId());

        return $impostor;
    }

    public function execute()
    {
        $impostor = $this->getImpostor($this->file);

        $event = new FileCopyEvent($this->file, $impostor);
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_COPY, $event);

        $event = new FolderEvent($this->folder);
        $this->eventDispatcher->dispatch(Events::FOLDER_BEFORE_WRITE_TO, $event);

        $this->backend->createFile($impostor, $this->folder);

        $event = new FileCopyEvent($this->file, $impostor);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_COPY, $event);

        return $this->fileRepository->createCommand(
            'Xi\Filelib\File\Command\AfterUploadFileCommand',
            array($impostor)
        )->execute();
    }

    public function getTopic()
    {
        return 'xi_filelib.command.file.copy';
    }
}
