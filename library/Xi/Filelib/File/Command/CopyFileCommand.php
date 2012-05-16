<?php

namespace Xi\Filelib\File\Command;

use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Event\FileCopyEvent;
use Serializable;
use InvalidArgumentException;


class CopyFileCommand extends AbstractFileCommand implements Serializable
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
     *
     * @var string
     */
    private $profile;

    public function __construct(FileOperator $fileOperator, File $file, Folder $folder)
    {
        parent::__construct($fileOperator);
        $this->file = $file;
        $this->folder = $folder;
    }

    /**
     * Generates name for a file copy
     *
     * @param string $oldName
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
     * Clones the original file and iterates the impostor's names until
     * a free one is found.
     *
     * @return File
     */
    public function getImpostor()
    {
        $impostor = clone $this->file;
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
        if (!$this->fileOperator->getAcl()->isFolderWritable($this->folder)) {
            throw new FilelibException("Folder '{$this->folder->getId()}'not writable");
        }

        $impostor = $this->getImpostor($this->file);

        $this->fileOperator->getBackend()->upload($impostor, $this->folder);
        $this->fileOperator->getStorage()->store($impostor, $this->fileOperator->getStorage()->retrieve($this->file));

        $event = new FileCopyEvent($this->file, $impostor);
        $this->fileOperator->getEventDispatcher()->dispatch('file.copy', $event);

        $command = $this->fileOperator->createCommand('Xi\Filelib\File\Command\AfterUploadFileCommand', array($this->fileOperator, $impostor));
        return $command->execute();
    }


    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->file = $data['file'];
        $this->folder = $data['folder'];
    }


    public function serialize()
    {
        return serialize(array(
           'file' => $this->file,
           'folder' => $this->folder,
        ));
    }



}
