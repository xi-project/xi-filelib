<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\File\File;

class SimpleRenderer extends AbstractRenderer
{
    /**
     * Returns url to a file
     *
     * @param  File   $file
     * @param  type   $options
     * @return string
     */
    public function getUrl(File $file, $options = array())
    {
        return '';
    }

    /**
     * Renders a file
     *
     * @param  File     $file    File
     * @param  array    $options Render options
     */
    public function render(File $file, array $options = array())
    {
        $options = $this->mergeOptions($options);

        $res = ($options['version'] === 'original') ?
            $this->getStorage()->retrieve($file->getResource()) :
            $this->getStorage()->retrieveVersion($file->getResource(), $options['version']);

        header("Content-Type: " . $file->getMimeType());
        echo $res->fpassthru();
    }





}

