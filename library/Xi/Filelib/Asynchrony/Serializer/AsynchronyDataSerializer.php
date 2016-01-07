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
use Xi\Filelib\Asynchrony\Serializer\DataSerializer\Serializer;
use Xi\Filelib\Attacher;
use Xi\Filelib\File\FileRepositoryInterface;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Identifiable;
use Xi\Filelib\LogicException;

class AsynchronyDataSerializer extends AbstractDataSerializer implements DataSerializer, Attacher
{
    /**
     * @var Serializer[]
     */
    private $serializers = [];

    public function addSerializer(Serializer $serializer)
    {
        $this->serializers[] = $serializer;
    }

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
    public function serialize($original)
    {
        $unserialized = clone $original;

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

        if (is_array($serializedCallback->callback)) {

            $object = $this->deserializeCallee($serializedCallback->callback[0]);
            if (!$object) {
                throw new LogicException(
                    sprintf("Unknown class '%s'", $serializedCallback->callback[0])
                );
            }
            $serializedCallback->callback[0] = $object;
        }

        $deserializedParams = [];
        foreach ($serializedCallback->params as $key => $param) {

            if (is_scalar($param) || is_array($param)) {
                $deserializedParams[$key] = $param;
            } elseif ($param instanceof SerializedIdentifiable) {

                $deserializedParam = $this->deserializeIdentifiable($param);
                if (!$deserializedParam) {
                    throw new LogicException('Unknown identifiable');
                }
                $deserializedParams[$key] = $deserializedParam;
            }
        }

        $serializedCallback->params = $deserializedParams;
        return $serializedCallback;
    }

    private function deserializeCallee($class) {

        foreach (array_reverse($this->serializers) as $serializer) {
            if ($ret = $serializer->deserializeCallee($class)) {
                return $ret;
            }
        }
        return false;
    }

    private function deserializeIdentifiable(SerializedIdentifiable $serialized)
    {
        foreach (array_reverse($this->serializers) as $serializer) {
            if ($ret = $serializer->deserializeIdentifiable($serialized)) {
                return $ret;
            }
        }
        return false;
    }
}
