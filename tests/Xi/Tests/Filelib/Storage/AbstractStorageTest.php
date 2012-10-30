<?php

namespace Xi\Tests\Filelib\Storage;

use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Exception\FileIOException;

class AbstractStorageTest extends \Xi\Tests\Filelib\TestCase
{

    private $storage;

    private $exception;

    public function setUp()
    {
        $this->storage = $this->getMockBuilder('Xi\Filelib\Storage\AbstractStorage')
                              ->setMethods(array('exists', 'versionExists', 'doRetrieve', 'doRetrieveVersion',
                                                 'doDelete', 'doDeleteVersion', 'doStore', 'doStoreVersion'))
                              ->getMock();

        $this->storage->expects($this->any())->method('exists')->will($this->returnValue(false));
        $this->storage->expects($this->any())->method('versionExists')->will($this->returnValue(false));

        $this->exception = new \Exception('Throw you like an exception');

        $this->resource = Resource::create();
        $this->version = 'version';
        $this->file = File::create(array());
    }

    /**
     * @test
     */
    public function storeShouldThrowCorrectException()
    {
        $this->storage->expects($this->once())->method('doStore')
                      ->will($this->throwException($this->exception));

        try {
            $this->storage->store($this->resource, '/lus/hof');

            $this->fail("Did not throw an exception");

        } catch (FileIoException $e) {
            $this->assertSame($this->exception, $e->getPrevious());
        }

    }

    /**
     * @test
     */
    public function storeVersionShouldThrowCorrectException()
    {
        $this->storage->expects($this->once())->method('doStoreVersion')
            ->will($this->throwException($this->exception));

        try {
            $this->storage->storeVersion($this->resource, $this->version, '/lus/hof');

            $this->fail("Did not throw an exception");

        } catch (FileIoException $e) {
            $this->assertSame($this->exception, $e->getPrevious());
        }

    }

    /**
     * @test
     * @expectedException Xi\Filelib\Exception\FileIOException
     */
    public function retrieveShouldThrowExceptionIfFileIsNotFound()
    {
        $this->storage->retrieve($this->resource);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\Exception\FileIOException
     */
    public function retrieveVersionsShouldThrowExceptionIfFileIsNotFound()
    {
        $this->storage->retrieveVersion($this->resource, 'version');
    }

    /**
     * @test
     * @expectedException Xi\Filelib\Exception\FileIOException
     */
    public function deleteShouldThrowExceptionIfFileIsNotFound()
    {
        $this->storage->delete($this->resource);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\Exception\FileIOException
     */
    public function deleteVersionsShouldThrowExceptionIfFileIsNotFound()
    {
        $this->storage->deleteVersion($this->resource, 'version');
    }


}