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

class Mob extends Character
{
    public $spawningX;
    public $spawningY;
    public $armorLevel;
    public $weaponLevel;
    public $hatelist;
    public $respawnTimeout;
    public $returnTimeout;
    public $isDead;
    public $respawnCallback;
    public $moveCallback;
    
    public function __construct($id, $kind, $x, $y)
    {
        parent::__construct($id, "mob", $kind, $x, $y);
        
        $this->updateHitPoints();
        $this->spawningX = $x;
        $this->spawningY = $y;
        $this->armorLevel = Properties::getArmorLevel($this->kind);
        $this->weaponLevel = Properties::getWeaponLevel($this->kind);
        $this->hatelist = array();
        $this->respawnTimeout = null;
        $this->returnTimeout = null;
        $this->isDead = false;
    }
    
    public function destroy()
    {
        $this->isDead = true;
        $this->hatelist = array();
        $this->clearTarget();
        $this->updateHitPoints();
        $this->resetPosition();
        
        $this->handleRespawn();
    }
    
    public function receiveDamage($points, $playerId)
    {
        $this->hitPoints -= $points;
    }
    
    public function hates($playerId)
    {
        return Utils::any($this->hatelist, function($obj) use($playerId)
        { 
            return $obj->id == $playerId; 
        });
    }
    
    public function increaseHateFor($playerId, $points)
    {
        if($this->hates($playerId)) 
        {
            $obj = Utils::detect($this->hatelist, function($obj) use($playerId)
            {
                return $obj->id == $playerId;
            });
            if($obj)
            {
                $obj->hate += $points;
            }
        }
        else 
        {
            $this->hatelist[] = (object)array('id'=>$playerId, 'hate'=> $points);
        }
        
        if($this->returnTimeout) {
            // Prevent the mob from returning to its spawning position
            // since it has aggroed a new player
            Timer::del($this->returnTimeout);
            $this->returnTimeout = null;
        }
    }
    
    public function getHatedPlayerId($hateRank)
    {
        $i = 0;
        $playerId = 0;
        $sorted = Utils::sortBy($this->hatelist, function($obj) {
            return $obj->hate;
        });
        $size = count($this->hatelist);
        
        if($hateRank && $hateRank <= $size) 
        {
            $i = $size - $hateRank;
        }
        else 
        {
            $i = $size - 1;
        }
        if($sorted && $sorted[$i]) 
        {
            $playerId = $sorted[$i]->id;
        }
        
        return $playerId;
    }
    
    public function forgetPlayer($playerId, $duration = 0)
    {
        $this->hatelist = Utils::reject($this->hatelist, function($obj) use ($playerId)
        {
            return $obj->id == $playerId;
        });
        
        if(empty($this->hatelist)) 
        {
            $this->returnToSpawningPosition($duration);
        }
    }
    
    public function forgetEveryone()
    {
        $this->hatelist = array();
        $this->returnToSpawningPosition(1);
    }
    
    public function drop($item)
    {
        if($item) 
        {
            return new Messages\Drop($this, $item);
        }
    }
    
    public function handleRespawn()
    {
        $delay = 30000;
        if(!empty($this->area) && $this->area instanceof MobArea) 
        {
            // Respawn inside the area if part of a MobArea
            $this->area->respawnMob($this, $delay);
        }
        else 
        {
            if(!empty($this->area) && $this->area instanceof ChestArea) 
            {
                $this->area->removeFromArea($this);
            }
        
            Timer::add($delay/1000, array($this, 'callback'), array(), false);
        }
    }
    
    public function callback()
    {
        if($this->respawnCallback)
        {
            call_user_func($this->respawnCallback);
        }
    }
    
    public function onRespawn($callback)
    {
        $this->respawnCallback = $callback;
    }
    
    public function resetPosition()
    {
        $this->setPosition($this->spawningX, $this->spawningY);
    }
    
    public function returnToSpawningPosition($waitDuration)
    {
        $delay = $waitDuration ?  $waitDuration : 4000;
        
        $this->clearTarget();
        
        $this->returnTimeout = Timer::add($delay/1000, array($this, 'timeoutCallback'), array(), false);
    }
    
    public function timeoutCallback()
    {
        $this->resetPosition();
        $this->move($this->x, $this->y);
    }
    
    public function onMove($callback)
    {
        $this->moveCallback  = $callback;
    }
    
    public function move($x, $y)
    {
        $this->setPosition($x, $y);
        if($this->moveCallback) 
        {
            call_user_func($this->moveCallback, $this);
        }
    }
    
    public function updateHitPoints()
    {
        $this->resetHitPoints(Properties::getHitPoints($this->kind));
    }
    
    public function distanceToSpawningPoint($x, $y)
    {
        return Utils::distanceTo($x, $y, $this->spawningX, $this->spawningY);
    }
}