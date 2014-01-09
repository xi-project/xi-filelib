<?php

namespace Xi\Filelib\Tool;

class ExtensionRequirements
{
    /**
     * @param $extension
     * @param $version
     * @throws \RuntimeException
     */
    public static function requireVersion($extension, $version = null)
    {
        $exception = new \RuntimeException(sprintf("Requires extension '%s', version %s", $extension, $version));

        if (!extension_loaded($extension)) {
            throw $exception;
        }

        if (!$version) {
            return;
        }

        if (version_compare(phpversion($extension), $version) === -1) {
            throw $exception;
        }
    }
}
