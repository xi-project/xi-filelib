<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\Slugifier;

use Zend\Filter\FilterChain;
use Zend\Filter\Alnum as AlnumFilter;
use Zend\Filter\Word\SeparatorToDash as SeparatorToDashFilter;
use Zend\Filter\StringToLower as StringToLowerFilter;
use Zend\Filter\Word\UnderscoreToDash as UnderscoreToDashFilter;
use Zend\Filter\Word\UnderscoreToSeparator;
/**
 * ZF2 slugifier
 */
class Zend2Slugifier extends AbstractZendSlugifier
{
    protected $filter;
    
    public function getFilter()
    {
        if (!$this->filter) {

            $filter = new FilterChain();
            $filter->attach(new UnderscoreToSeparator(' '));
            $filter->attach(new AlnumFilter(true));
            $filter->attach(new SeparatorToDashFilter(' '));
            $filter->attach(new StringToLowerFilter(array('encoding' => 'utf-8')));

            $this->filter = $filter;
        }
        return $this->filter;
    }
}
