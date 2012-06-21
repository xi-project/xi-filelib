<?php

namespace Xi\Filelib\Tool\Transliterator;

interface Transliterator
{

    /**
     * Transliterates a given string
     *
     * @param string $str
     * @return string
     */
    public function transliterate($str);

}