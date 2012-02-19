<?php

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\Plugin\Plugin;

class PluginEvent extends Event
{
    /**
     * @var Plugin
     */
    private $plugin;
    
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
    
    /**
     * Returns plugin
     * 
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
    
}
