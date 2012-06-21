<?php

namespace Xi\Filelib\Tool\Slugifier;

use Xi\Filelib\Tool\Transliterator\Transliterator;

/**
 * Abstract Zend Framework slugifier
 */
abstract class AbstractZendSlugifier implements Slugifier
{
    /**
     * @var Transliterator
     */
    private $transliterator;

    public function __construct(Transliterator $transliterator)
    {
        $this->transliterator = $transliterator;
    }

    abstract public function getFilter();

    /**
     * @return Transliterator
     */
    public function getTransliterator()
    {
        return $this->transliterator;
    }

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
        $slugged = $this->getTransliterator()->transliterate($unslugged);
        $slugged = $this->getFilter()->filter($slugged);
        return $slugged;
    }
}
