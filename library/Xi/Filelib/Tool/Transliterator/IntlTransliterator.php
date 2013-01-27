<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\Transliterator;

use \Transliterator as InnerTransliterator;

/**
 * Intl transliterator
 */
class IntlTransliterator implements Transliterator
{

    /**
     * @see Transliterator::transliterate()
     */
    public function transliterate($str)
    {
        $rule = 'NFD; [:Nonspacing Mark:] Remove; NFC';
        $transliterator = InnerTransliterator::create($rule);

        return $transliterator->transliterate($str);
    }

}
