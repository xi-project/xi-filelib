<?php

use Xi\Filelib\Plugin\VersionProvider\LazyVersionProvider;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\Plugin;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lazy-publisher-common.php';

$filelib->getFileRepository()->findAll()->each(

    function (File $file) use ($filelib) {

        $filelib->getPluginManager()->getPlugins()->filter(function (Plugin $plugin) {
            return $plugin instanceof LazyVersionProvider;
        })->each(function (LazyVersionProvider $plugin, $key, $file) {
                $plugin->deleteProvidedVersions($file);
                $plugin->deleteProvidedVersions($file->getResource());
            }, $file);

        $filelib->getFileRepository()->update($file);
    }
);
