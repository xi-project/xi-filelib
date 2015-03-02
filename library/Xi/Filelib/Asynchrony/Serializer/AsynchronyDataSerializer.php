<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony\Serializer;

use Pekkis\Queue\Data\AbstractDataSerializer;
use Pekkis\Queue\Data\DataSerializer;
use Xi\Filelib\Attacher;
use Xi\Filelib\File\FileRepositoryInterface;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Identifiable;
use Xi\Filelib\LogicException;

class AsynchronyDataSerializer extends AbstractDataSerializer implements DataSerializer, Attacher
{
    /**
     * @var FileRepositoryInterface
     */
    private $fileRepository;

    public function attachTo(FileLibrary $filelib)
    {
        $this->fileRepository = $filelib->getFileRepository();
    }

    /**
     * @param SerializedCallback $unserialized
     * @return bool
     */
    public function willSerialize($unserialized)
    {
        return ($unserialized instanceof SerializedCallback);
    }

    /**
     * @param SerializedCallback $unserialized
     * @return string
     */
    public function serialize($unserialized)
    {
        if (is_array($unserialized->callback)) {
            $unserialized->callback[0] = get_class($unserialized->callback[0]);
        }

        $serializedParams = [];
        foreach ($unserialized->params as $key => $param) {

            if (is_scalar($param) || is_array($param)) {
                $serializedParams[$key] = $param;
            } elseif ($param instanceof Identifiable) {
                $serializedParams[$key] = new SerializedIdentifiable($param);
            }
        }

        $unserialized->params = $serializedParams;

        return serialize($unserialized);
    }

    /**
     * @param string $serialized
     * @return SerializedCallback
     */
    public function unserialize($serialized)
    {
        /** @var SerializedCallback $serializedCallback */
        $serializedCallback = unserialize($serialized);

        if (is_array($serializedCallback->callback[0])) {
            switch ($serializedCallback->callback[0]) {

                case 'Xi\Filelib\File\FileRepository':
                    $substitute = $this->fileRepository;
                    break;

                default:
                    throw new LogicException('Unknown class');

            }
            $serializedCallback->callback[0] = $substitute;
        }


        $deserializedParams = [];
        foreach ($serializedCallback->params as $key => $param) {

            if (is_scalar($param) || is_array($param)) {
                $deserializedParams[$key] = $param;
            } elseif ($param instanceof SerializedIdentifiable) {
                $deserializedParams[$key] = $this->deserializeIdentifiable($param);
            }
        }

        $serializedCallback->params = $deserializedParams;
        return $serializedCallback;
    }

    private function deserializeIdentifiable(SerializedIdentifiable $serialized)
    {
        switch ($serialized->className) {

            case 'Xi\Filelib\File\File':
                return $this->fileRepository->find($serialized->id);
                break;

            default:
                throw new LogicException('Unknown identifiable');

        }

    }
}
