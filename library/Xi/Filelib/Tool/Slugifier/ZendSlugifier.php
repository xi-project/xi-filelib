<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\Slugifier;

/**
 * ZF1 slugifier
 */
class ZendSlugifier extends AbstractZendSlugifier
{
    protected $filter;
    
    public function getFilter()
    {
        if (!$this->filter) {
            $filter = new \Zend_Filter();
            $filter->addFilter(new \Zend_Filter_Word_UnderscoreToSeparator(' '));
            $filter->addFilter(new \Zend_Filter_Alnum(true));
            $filter->addFilter(new \Zend_Filter_Word_SeparatorToDash(' '));
            $filter->addFilter(new \Zend_Filter_StringToLower(array('encoding' => 'utf-8')));

            $this->filter = $filter;
        }
        return $this->filter;
    }
}
