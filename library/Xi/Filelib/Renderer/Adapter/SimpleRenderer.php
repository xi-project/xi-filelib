<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\File\File;

class SimpleRenderer extends AbstractRenderer
{
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

        $this->dispatchRenderEvent($file);

        header("Content-Type: " . $file->getMimeType());
        echo $res->fpassthru();

    }





}

