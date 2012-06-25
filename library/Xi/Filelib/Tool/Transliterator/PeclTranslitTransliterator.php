<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\Transliterator;

/**
 * Intl transliterator
 */
class PeclTranslitTransliterator implements Transliterator
{

    /**
     * @see Transliterator::transliterate()
     */
    public function transliterate($str)
    {
        $res = transliterate(
            $str,
            array(
                'diacritical_remove'
            ),
            'utf-8', 'utf-8'
        );

        return $res;
    }

}
