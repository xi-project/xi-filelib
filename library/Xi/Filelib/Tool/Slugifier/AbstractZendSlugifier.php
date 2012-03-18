<?php

namespace Xi\Filelib\Tool\Slugifier;

/**
 * Abstract Zend Framework slugifier
 */
abstract class AbstractZendSlugifier implements Slugifier
{
    abstract public function getFilter();
        
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
        $filter = $this->getFilter();
        
        $slugged = iconv('UTF-8', 'ASCII//TRANSLIT', $unslugged);
        $slugged = $filter->filter($slugged);
        
        return $slugged;
    }
}
