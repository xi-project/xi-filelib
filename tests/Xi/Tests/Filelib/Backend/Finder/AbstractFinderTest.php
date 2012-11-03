<?php

namespace Xi\Tests\Filelib\Backend\Finder;

use Xi\Tests\Filelib\TestCase;
use Xi\Filelib\Backend\Finder\AbstractFinder;
use Xi\Filelib\Backend\Finder\Finder;

class AbstractFinderTest extends TestCase
{

    protected $finder;

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Backend\Finder\AbstractFinder'));
        $this->assertContains('Xi\Filelib\Backend\Finder\Finder', class_implements('Xi\Filelib\Backend\Finder\AbstractFinder'));
    }


    public function setUp()
    {
        $finder = $this->getMockBuilder('Xi\Filelib\Backend\Finder\AbstractFinder')
                        ->setMethods(array('getFields', 'getResultClass'))
                        ->getMockForAbstractClass();

        $finder->expects($this->any())->method('getFields')->will($this->returnValue(array('lussi', 'tussi')));
        $finder->expects($this->any())->method('getResultClass')->will($this->returnValue('Tussin\Lussuttaja'));

        $this->finder = $finder;
    }

    /**
     * @test
     */
    public function addParameterShouldThrowExceptionIfFieldDoesNotExist()
    {
        $this->setExpectedException('Xi\Filelib\Backend\Finder\FinderException');
        $this->finder->addParameter('sugen_sie', 'xooxoo');
    }

    /**
     * @test
     */
    public function addParameterShouldAddParameter()
    {
        $params = array(
            'lussi' => 'luudenford',
            'tussi' => 'luudendorf'
        );

        foreach ($params as $key => $param) {
            $this->finder->addParameter($key, $param);
        }

        $this->assertEquals($params, $this->finder->getParameters());

    }


}
