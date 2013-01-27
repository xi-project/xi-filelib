<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\MimeTypeResolver;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class SymfonyMimeTypeResolver implements MimeTypeResolver
{

    private $mimeTypeGuesser;

    /**
     *
     * @return MimeTypeGuesser
     */
    private function getMimeTypeGuesser()
    {
        if (!$this->mimeTypeGuesser) {
            $this->mimeTypeGuesser = MimeTypeGuesser::getInstance();
        }

        return $this->mimeTypeGuesser;
    }

    public function resolveMimeType($path)
    {
        $mimeType = $this->getMimeTypeGuesser()->guess($path);

        return $mimeType;
    }

}
