<?php

namespace Xi\Filelib\Tool;

use \Zend\Filter\FilterChain,
    \Zend\Filter\Alnum as AlnumFilter,
    \Zend\Filter\Word\SeparatorToDash as SeparatorToDashFilter,
    \Zend\Filter\StringToLower as StringToLowerFilter,
    \Zend\Filter\Word\UnderscoreToDash as UnderscoreToDashFilter
    ;


class Slugifier {

    public function slugifyPath($path)
    {
        $path = explode('/', $path);
        
        $ret = array();
        foreach ($path as $fragment) {
            $ret[] = $this->slugify($fragment);
        }        

        return implode('/', $ret);
        
    }
    
    
    public function slugify($unslugged)
    {
        // $folderUrl = explode(DIRECTORY_SEPARATOR, $folderUrl);
                
        $filter = new FilterChain();
        $filter->attach(new \Zend\Filter\Word\UnderscoreToSeparator(' '));
        $filter->attach(new AlnumFilter(true));
        $filter->attach(new SeparatorToDashFilter(' '));
        $filter->attach(new StringToLowerFilter(array('encoding' => 'utf-8')));

        $slugged = iconv('UTF-8', 'ASCII//TRANSLIT', $unslugged);
        
        $slugged = $filter->filter($slugged);
        
        return $slugged;
        
        
                
    }
    
        
    
    
}
