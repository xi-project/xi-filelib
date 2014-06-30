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

    /**
     * @test
     */
    public function has()
    {
        $data = new IdentifiableDataContainer();
        $this->assertFalse($data->has('tenhunen'));

        $data->set('tenhunen', 'suurmies');
        $this->assertTrue($data->has('tenhunen'));
    }

    /**
     * @test
     */
    public function invalidKeyThrowsUp()
    {
        $this->setExpectedException('Xi\Filelib\InvalidArgumentException');
        $data = new IdentifiableDataContainer();
        $data->set('tenhusen suuruus on käsittämätön', 'potenssiin kolme!');
    }
}
