<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\IdentifiableDataContainer;

class IdentifiableDataContainerTest extends TestCase
{

    /**
     * @test
     */
    public function convertsToArray()
    {
        $data = new IdentifiableDataContainer();
        $this->assertEquals(array(), $data->toArray());

        $array = array('tusso' => 'gran lusso');
        $data = new IdentifiableDataContainer($array);
        $this->assertEquals($array, $data->toArray());
    }

    /**
     * @test
     */
    public function returnsDefaultValue()
    {
        $data = new IdentifiableDataContainer();

        $this->assertNull($data->get('tenhusen-jarkevat-ajatukset'));
        $this->assertSame(0, $data->get('tenhusen-suuret-saavutukset', 0));
    }

    /**
     * @test
     */
    public function sets()
    {
        $data = new IdentifiableDataContainer();

        $ret = $data->set('tenhunen', 'lipaiseepi');
        $this->assertSame($data, $ret);

        $this->assertEquals('lipaiseepi', $data->get('tenhunen'));
    }
}
