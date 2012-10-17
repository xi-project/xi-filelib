<?php

namespace Xi\Tests\Filelib\Plugin;

use Xi\Filelib\Plugin\ConsoleHelper;
use Xi\Filelib\Exception\InvalidArgumentException;

class ConsoleHelperTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @test
     */
    public function testExecuteCallsCommand()
    {
        $exp = 'PHP ' . phpversion();
        $cmd = new ConsoleHelper('php');

        $act = $cmd->execute('-v')[0];
        $this->assertEquals($exp, substr($act, 0, strlen($exp)));
    }

    public static function invalidCommandsProvider()
    {
        return array(
            array('foo bar'),
            array('-foo'),
            array('foo#bar'),
            array('foo=bar'),
            array('foo\'bar')
        );
    }

    /**
     * @test
     * @dataProvider invalidCommandsProvider
     * @expectedException InvalidArgumentException
     */
    public function testExecuteThrowsOnInvalidCommandNames($cmd)
    {
        new ConsoleHelper($cmd, $this->localeExists('en_US.UTF-8'));
    }

    /**
     * @test
     */
    public function testExecuteAcceptsUnicodeAlphabeticChars()
    {
        $cmd = new ConsoleHelper('böö', $this->localeExists('en_US.UTF-8'));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function testExecuteThrowsOnInvalidLocale()
    {
        new ConsoleHelper('foo', 'nonexistent locale');
    }

    private function localeExists($locale)
    {
        if (!setlocale(LC_ALL, $locale)) {
            $this->markTestSkipped(sprinf("Test needs locale '%s' to be installed on your system.", $locale));
        }
        return $locale;
    }

}
