<?php

namespace Xi\Filelib\Asynchrony\Serializer\DataSerializer;

use Xi\Filelib\Asynchrony\Serializer\SerializedIdentifiable;
use Xi\Filelib\Attacher;
use Xi\Filelib\File\FileRepositoryInterface;
use Xi\Filelib\FileLibrary;

class RepositorySerializer implements Serializer, Attacher
{
    /**
     * @var FileRepositoryInterface
     */
    private $fileRepository;

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->fileRepository = $filelib->getFileRepository();
    }

    public function deserializeCallee($class)
    {
        switch ($class) {

            case 'Xi\Filelib\File\FileRepository':
                return $this->fileRepository;
                break;

            default:
                return false;
        }
    }

    public function deserializeIdentifiable(SerializedIdentifiable $serialized)
    {
        switch ($serialized->className) {

            case 'Xi\Filelib\File\File':
                return $this->fileRepository->find($serialized->id);
                break;

            default:
                return false;
        }
    }
}
