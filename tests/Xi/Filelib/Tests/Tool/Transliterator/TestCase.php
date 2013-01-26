<?php

namespace Xi\Filelib\Tests\Tool\Transliterator;

use Xi\Filelib\Tool\Transliterator\Transliterator;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @abstract
     * @return Transliterator
     */
    abstract public function getTransliteratorWithDefaultSettings();

    /**
     * @return array
     */
    public function provideBasicStrings()
    {
        return array(
            array('Ainoo mi toimii on vibraato.', 'Ainoo mi toimii on vibraato.'),
            array('Tohtori Vesala imaisi mehevän tussin', 'Tohtori Vesala imaisi mehevan tussin'),
            array('Vesihiisi sihisi hississä', 'Vesihiisi sihisi hississa'),
            array('Ääliö älä lyö ööliä läikkyy', 'Aalio ala lyo oolia laikkyy'),
            array('ÖÄÅöäå', 'OAAoaa'),
        );
    }


    /**
     * @dataProvider provideBasicStrings
     * @test
     */
    public function transliteratorShouldCorrectlyTransliterateBasicStrings($untransliterated, $transliterated)
    {
        $transliterator = $this->getTransliteratorWithDefaultSettings();
        $this->assertEquals($transliterated, $transliterator->transliterate($untransliterated));
    }



}
