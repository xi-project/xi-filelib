<?php

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
