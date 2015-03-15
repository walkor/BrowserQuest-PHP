<?php 
namespace Server\WS;

class MultiVersionWebsocketServer extends \Server\Server
{
    public $worlizeServerConfig = array();
    public $_connections = array();
    public $_counter = array();
    
    public function __construct()
    {
        
    }
}