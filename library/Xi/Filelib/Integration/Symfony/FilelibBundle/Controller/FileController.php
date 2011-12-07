<?php

namespace Xi\Filelib\Integration\Symfony\FilelibBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    
    
    public function renderAction($id, $version = 'original', $download = false)
    {
        $fl = $this->get('filelib');

        $file = $fl->file()->find($id);
        if(!$file) {
            return $this->createNotFoundException();
            // throw new Emerald_Common_Exception('File not found', 404);
        }

        $opts = array();

        if ($version && $version != 'original') {
            $opts['version'] = $version;
        }
        
        if ($download) {
            $opts['download'] = true;
        }
        
        // When readable by anonymous, redirect to pretty url
        if ($fl->file()->isReadableByAnonymous($file)) {
            $url = $fl->file()->getUrl($file, $opts);
            return $this->redirect($url, 302);
        }
        
        // Convert all exceptions to 404's
        try {
            
            $response = new Response();
            
            
            if (isset($opts['download'])) {
                $response->headers->set('Content-disposition', "attachment; filename={$file->getName()}");
            }

            $response->headers->set('Content-Type', $file->getMimetype());
            
            $response->setContent($fl->file()->render($file, $opts));
            
            return $response;
            
        } catch (\Exception $e) {
           return $this->createNotFoundException();
        }

        
    }
    
    
    
    
    
    
    
}

