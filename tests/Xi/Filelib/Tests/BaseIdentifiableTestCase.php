<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Identifiable;

abstract class BaseIdentifiableTestCase extends TestCase
{
    /**
     * @return string
     */
    abstract public function getClassName();

    /**
     * @return Identifiable
     */
    public function getInstance($args = array())
    {
        $refl = new \ReflectionClass($this->getClassName());
        $creator = $refl->getMethod('create');
        return $creator->invoke(null, $args);
    }

    /**
     * @test
     */
    public function clonesDeeply()
    {
        $source = $this->getInstance();

        $sourceData = $source->getData();
        $sourceData->set('lussutappa', 'tussia');

        $target = clone $source;
        $targetData = $target->getData();

        $this->assertEquals($source->getData()->toArray(), $target->getData()->toArray());
        $this->assertNotSame($sourceData, $targetData);
    }

    /**
     * @test
     */
    public function getDataShouldReturnACachedArrayObject()
    {
        $file = $this->getInstance();
        $data = $file->getData();

        $this->assertInstanceOf('Xi\Filelib\IdentifiableDataContainer', $data);
        $data->set('tussi', 'lussi');

        $this->assertSame($data, $file->getData());

    }

    /**
     * @test
     */
    public function gettingAndSettingDataWorks()
    {
        $file = $this->getInstance();

        $data = $file->getData();
        $this->assertInstanceOf('Xi\Filelib\IdentifiableDataContainer', $data);

        $file->setData(array('lusso' => 'magnifico'));

        $data = $file->getData();
        $this->assertEquals('magnifico', $data->get('lusso'));
    }


}
