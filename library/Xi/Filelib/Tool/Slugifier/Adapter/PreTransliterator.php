<?php

namespace Xi\Filelib\Tool\Slugifier\Adapter;

use Xi\Transliterator\Transliterator;

class PreTransliterator implements SlugifierAdapter
{
    /**
     * @param Transliterator $transliterator
     * @param SlugifierAdapter $adapter
     */
    public function __construct(Transliterator $transliterator, SlugifierAdapter $adapter)
    {
        $this->transliterator = $transliterator;
        $this->adapter = $adapter;
    }

    /**
     * @param string $unslugged
     * @return string
     */
    public function slugify($unslugged)
    {
        return $this->adapter->slugify($this->transliterator->transliterate($unslugged));
    }
}
