<?php 
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
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
        $mob = new Mob('1' . $this->id . ''. $k . ''. count($this->entities), $k, $pos['x'], $pos['y']);
        
        // @todo bind
        $mob->onMove(array($this->world, 'onMobMoveCallback'));

        return $mob;
    }
    
    public function respawnMob($mob, $delay)
    {
        $this->removeFromArea($mob);
        Timer::add($delay/1000, array($this, 'respawnMobCallback'), array($mob), false);
    }
    
    public function respawnMobCallback($mob)
    {
        $pos = $this->_getRandomPositionInsideArea();
        $mob->x = $pos['x'];
        $mob->y = $pos['y'];
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