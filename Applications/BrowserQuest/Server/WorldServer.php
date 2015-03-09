<?php 
namespace Server;
use \Workerman\Worker;
require_once __DIR__ . 'Constants.php';

class WorldServer extends Worker
{
    public $entities = array();
    public $players = array();
    public $mobs = array();
    public $attackers = array();
    public $items = array();
    public $equipping = array();
    public $hurt = array();
    public $npcs = array();
    public $mobAreas = array();
    public $chestAreas = array();
    public $groups = array();
    public $outgoingQueues = array();
    
    public function __construct($socket_name)
    {
        $this->onConnect = array($this, 'onConnect');
        parent::__construct($socket_name);
    }
    
    public function onConnect($connection)
    {
        $connection->onWebSocketConnect = array($this, 'onWebSocketConnect');
    }
    
    public function onWebSocketConnect($connection)
    {
        
    }
}