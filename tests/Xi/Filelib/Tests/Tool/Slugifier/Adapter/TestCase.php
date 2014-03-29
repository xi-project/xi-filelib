<?php

namespace Xi\Filelib\Tests\Tool\Slugifier\Adapter;

use Xi\Filelib\Tool\Slugifier\Adapter\PreTransliterator;
use Xi\Filelib\Tool\Slugifier\Adapter\SlugifierAdapter;
use Xi\Transliterator\StupidTransliterator;

abstract class TestCase extends \Xi\Filelib\Tests\TestCase
{
    /**
     *
     * @var SlugifierAdapter
     */
    protected $slugifier;

    /**
     * @return array
     */
    public function provideSluggables()
    {
        return array(
            array('lußutappa tussia, losokkaiseni, tai alkaa ööli läikkyä!'),
            array('Polska, wielkość piosenki kultu'),
            array('Castellano, la magnitud de la canción de adoración'),
            array('Je größer die Menge des Lobes für den deutschen String'),
            array('Hrvatska, količina hvale sočne sage'),
        );
    }

    /**
     * @test
     * @dataProvider provideSluggables
     */
    public function slugifierShouldSlugifyReasonablyWeirdLatinCharactersToLowerCaseAsciiWithDashes($weird)
    {
        $unweird = $this->slugifier->slugify($weird);
        $this->assertRegExp('#[a-z-]+#', $unweird);
    }

    /**
     * @test
     * @dataProvider provideSluggables
     */
    public function slugifierShouldCooperateWithPreTransliterator($weird)
    {
        $preTransliterator = new PreTransliterator(
            new StupidTransliterator(),
            $this->slugifier
        );

        $unweird = $preTransliterator->slugify($weird);
        $this->assertRegExp('#[a-z-]+#', $unweird);
    }


    /**
     * @test
     */
    public function slugifierShouldSlugifySimpleNonUnicodeStringsProperly()
    {
        $this->assertEquals('peksu-con', $this->slugifier->slugify('peksu con'));
        $this->assertEquals('lussuti-lussuti', $this->slugifier->slugify('lussuti_lussuti'));
        $this->assertEquals('suuren-ugrilaisen-kansan-sielu', $this->slugifier->slugify('SUUrEN ugRILAIseN kanSAn SIELU'));
    }
}
