<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Platform;

use Xi\Filelib\Backend\Finder\Finder;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Filelib\Backend\Finder\ResourceFinder;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Tool\UuidGenerator\PHPUuidGenerator;
use Xi\Filelib\Exception\InvalidArgumentException;
use Xi\Filelib\Exception\FolderNotFoundException;
use Xi\Filelib\Exception\FolderNotEmptyException;
use Xi\Filelib\Exception\NonUniqueFileException;
use Xi\Filelib\Exception\ResourceReferencedException;
use Xi\Filelib\Event\ResourceEvent;
use Exception;

/**
 * Abstract backend implementing common methods
 *
 * @author pekkis <pekkisx@gmail.com>
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 */
abstract class AbstractPlatform implements Platform
{
    /**
     * @var PHPUuidGenerator
     */
    private $uuidGenerator;

    public function generateUuid()
    {
        if (!$this->uuidGenerator) {
            $this->uuidGenerator = new PHPUuidGenerator();
        }
        return $this->uuidGenerator->v4();
    }


    /**
     * @param  mixed                    $id
     * @param  string                   $message
     * @return InvalidArgumentException
     */
    protected function createInvalidArgumentException($id, $message)
    {
        return new InvalidArgumentException(sprintf(
            $message,
            $id
        ));
    }

    /**
     * @param  File                   $file
     * @param  Folder                 $folder
     * @throws NonUniqueFileException
     *
     * @internal Should be protected but can't because of PHP 5.3 closure scope
     */
    public function throwNonUniqueFileException(File $file, Folder $folder)
    {
        throw new NonUniqueFileException(sprintf(
            'A file with the name "%s" already exists in folder "%s"',
            $file->getName(),
            $folder->getName()
        ));
    }

}
