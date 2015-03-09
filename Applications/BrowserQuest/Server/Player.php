<?php 
namespace Server;
use \Workerman\Connection\TcpConnection;
use \Workerman\Lib\Timer;

class Player extends Character
{
    public $hasEnteredGame = false;
    public $isDead = false;
    public $haters = array();
    public $lastCheckpoint = array();
    public $disconnectTimeout = 0;
    public $armor = 0;
    public $armorLevel = 0;
    
    
    public function __construct(TcpConnection $connection, $worldServer)
    {
        $this->server = $worldServer;
        $this->connection = $connection;
        $this->connection->onMessage = array($this, 'onMessage');
    }
    
    public function onMessage($connection, $data)
    {
        $message = json_decode($data, true);
        $action = $message[0];
        
        if(!$this->hasEnteredGame && $action !== TYPES_MESSAGES_HELLO)
        {
            $this->connection->close("Invalid handshake message: ". $data);
            return;
        }
        
        $this->resetTimeout();
        
        if($action === TYPES_MESSAGES_HELLO) 
        {
            $name = $message[1];
            $this->name = $name === "" ? "lorem ipsum" : $name;
            $this->kind = TYPES_ENTITIES_WARRIOR;
            
        }
    }
    
    public function equipArmor($kind)
    {
        $this->armor = $kind;
        //$this->armorLevel = 
    }
    
    public function resetTimeout()
    {
        Timer::del($this->disconnectTimeout);
        // 15分钟
        $this->disconnectTimeout = Timer::add(15, array($this, 'timeout'), false);
    }
    
    public function timeout()
    {
        $this->connection->send('timeout');
        $this->connection->close('Player was idle for too long');
    }
}