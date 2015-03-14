<?php 
namespace Server;
use \Workerman\Lib\Timer;

class MobArea extends Area
{
    public $nb;
    public $kind;
    public $respawns;
    public function __construct($id, $nb, $kind, $x, $y, $width, $height, $world)
    {
        parent::__construct($id, $x, $y, $width, $height, $world);
        $this->nb = $nb;
        $this->kind = $kind;
        $this->respawns = array();
        $this->setNumberOfEntities($nb);
    }
   
   public function spawnMobs()
   {
       for($i = 0; $i < $this->nb; $i += 1) 
       {
           $this->addToArea($this->_createMobInsideArea());
       }
   }
   
   public function _createMobInsideArea() 
   {
        $k = Types::getKindFromString($this->kind);
        $pos = $this->_getRandomPositionInsideArea();
        $mob = new Mob('1' . $this->id . ''. k + ''. $this->entities->length, $k, $pos['x'], $pos['y']);
        
        // @todo bind
        $mob->onMove($this->world->onMobMoveCallback->bind($this->world));

        return mob;
    }
    
    public function respawnMob($mob, $delay)
    {
        $this->removeFromArea($mob);
        Timer::add($delay/1000, array($this, 'respawnMobCallback'), array($mob), false);
    }
    
    public function respawnMobCallback($mob)
    {
        $pos = $this->_getRandomPositionInsideArea();
        $mob->x = $pos->x;
        $mob->y = $pos->y;
        $mob->isDead = false;
        $this->addToArea($mob);
        $this->world->addMob($mob);
    }
    
    public function initRoaming($mob)
    {
        Timer::add(0.5, array($this, 'initRoamingCallback'));
    }
    
    public function initRoamingCallback()
    {
        array_walk($this->entities, array($this, 'initRoamingEachCallback'));
    }
    
    public function initRoamingEachCallback($mob)
    {
        $canRoam = rand(0, 20) === 1;
        if($canRoam) 
        {
            if(!$mob->hasTarget() && !$mob->isDead) 
            {
                $pos = $this->_getRandomPositionInsideArea();
                $mob->move($pos['x'], $pos['y']);
            }
        }
    }
    
    public function createReward()
    {
        $pos = $this->_getRandomPositionInsideArea();
        return array('x'=>$pos['x'], 'y'=> $pos['y'], 'kind'=>TYPES_ENTITIES_CHEST);
    }
}