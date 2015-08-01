<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File;

use DateTime;
use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\Event\FileCopyEvent;
use Xi\Filelib\Event\FolderEvent;
use Xi\Filelib\Events;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Resource\ResourceRepository;
use Xi\Filelib\Storage\Storage;

class FileCopier
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
     * @var ResourceRepository
     */
    private $resourceRepository;

    /**
     * @var Storage
     */
    private $storage;

    public function __construct(
        ResourceRepository $resourceRepository,
        FileRepository $fileRepository,
        Storage $storage
    ) {
        $this->resourceRepository = $resourceRepository;
        $this->fileRepository = $fileRepository;
        $this->storage = $storage;
    }


    public function copy(File $file, Folder $folder)
    {
        return $this->getCopy($file, $folder);
    }

    /**
     * Generates name for a file copy
     *
     * @param  string                   $oldName
     * @return string
     * @throws InvalidArgumentException
     */
    private function getCopyName($oldName)
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
    private function getCopy(File $file, Folder $folder)
    {
        $impostor = clone $file;
        $impostor->setUuid(Uuid::uuid4());

        foreach ($impostor->getVersions() as $version) {
            $impostor->removeVersion($version);
        }

        $found = $this->fileRepository->findByFilename($folder, $impostor->getName());

        if (!$found) {
            return $impostor;
        }

        do {
            $impostor->setName($this->getCopyName($impostor->getName()));
            $found = $this->fileRepository->findByFilename($folder, $impostor->getName());
        } while ($found);

        $impostor->setFolderId($folder->getId());

        return $impostor;
    }
}
