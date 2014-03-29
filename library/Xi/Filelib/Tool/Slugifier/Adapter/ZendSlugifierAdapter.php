<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\Slugifier\Adapter;

use Zend\Filter\FilterChain;
use Zend\I18n\Filter\Alnum as AlnumFilter;
use Zend\Filter\Word\SeparatorToDash as SeparatorToDashFilter;
use Zend\Filter\StringToLower as StringToLowerFilter;
use Zend\Filter\Word\UnderscoreToSeparator;
use Xi\Transliterator\Transliterator;

/**
 * Zend Framework 2 slugifier
 *
 * @deprecated
 */
class ZendSlugifierAdapter implements SlugifierAdapter
{
    private $filter;

    /**
     * @var Transliterator
     */
    private $transliterator;

    public function __construct()
    {
        // $this->inner = new PreTransliterator($transliterator, $this);
    }

    /**
     * @param string $unslugged
     * @return string
     */
    public function slugify($unslugged)
    {
        // $slugged = $this->transliterator->transliterate($unslugged);
        $slugged = $this->getFilter()->filter($unslugged);
        return $slugged;
    }

    /**
     * @return FilterChain
     */
    private function getFilter()
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
