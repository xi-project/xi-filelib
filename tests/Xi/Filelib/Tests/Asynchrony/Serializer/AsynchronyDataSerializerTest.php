<?php

namespace Xi\Filelib\Tests\Asynchrony\Serializer;

use Xi\Filelib\Asynchrony\Serializer\AsynchronyDataSerializer;
use Xi\Filelib\Asynchrony\Serializer\DataSerializer\RepositorySerializer;
use Xi\Filelib\Asynchrony\Serializer\SerializedCallback;
use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Tests\Storage\Adapter\MemoryStorageAdapter;

class AsynchronyDataSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function serializesAndUnserializesUpload()
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter()
        );

        $serializer = new AsynchronyDataSerializer();
        $serializer->attachTo($filelib);

        $unserializer = new RepositorySerializer();
        $unserializer->attachTo($filelib);

        $serializer->addSerializer($unserializer);

        $unserializedCallback = new SerializedCallback(
            [$filelib->getFileRepository(), 'upload'],
            [ROOT_TESTS . '/data/self-lussing-manatee.jpg']
        );

        $this->assertTrue($serializer->willSerialize($unserializedCallback));

        $serializedCallback = $serializer->serialize($unserializedCallback);
        $deserializedCallback = $serializer->unserialize($serializedCallback);
        $this->assertEquals($unserializedCallback, $deserializedCallback);
    }

    /**
     * @test
     */
    public function serializesAndUnserializesAfterUpload()
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter()
        );

        $file = $filelib->getFileRepository()->upload(
            ROOT_TESTS . '/data/self-lussing-manatee.jpg'
        );


        $serializer = new AsynchronyDataSerializer();
        $serializer->attachTo($filelib);

        $unserializer = new RepositorySerializer();
        $unserializer->attachTo($filelib);
        $serializer->addSerializer($unserializer);

        $unserializedCallback = new SerializedCallback(
            [$filelib->getFileRepository(), 'afterUpload'],
            [$file]
        );

        $this->assertTrue($serializer->willSerialize($unserializedCallback));

        $serializedCallback = $serializer->serialize($unserializedCallback);
        $deserializedCallback = $serializer->unserialize($serializedCallback);
        $this->assertEquals($unserializedCallback, $deserializedCallback);
    }
}
