<?php 
namespace Server;
use \Workerman\Lib\Timer;

class Item extends Entity
{
    public $isStatic = false;
    public $isFromChest = false;
    public $blinkTimeout = 0;
    public $despawnTimeout = 0;
    public $respawnCallback = null;
    public function __construct($id, $kind, $x, $y)
    {
        parent::__construct($id, 'item', $kind, $x, $y);
    }
    
    public function handleDespawn($params)
    {
        $this->$blinkTimeout = Timer::add($params['beforeBlinkDelay']/1000, function() use ($params){
            call_user_func($params['blinkCallback']);
            $this->despawnTimeout = Timer::add($params['blinkingDuration']/1000, 
                    $params['despawnCallback'], 
                    array(),
                     false
                    );
        }, 
        array(), 
        false
        );
    }
    
    public function destroy()
    {
        if($this->blinkTimeout)
        {
            Timer::del($this->blinkTimeout);
        }
        if($this->despawnTimeout)
        {
            Timer::del($this->despawnTimeout);
        }
        if($this->isStatic)
        {
            $this->scheduleRespawn(30000);
        }
    }
    
    public function scheduleRespawn($delay)
    {
        Timer::add($delay/1000, function($self){
            if($self->respawnCallback)
            {
                call_user_func($self->respawnCallback);
            }
        }, array($this), false);
    }
    
    public function onRespawn($callback)
    {
        $this->respawnCallback = $callback;
    }
}
