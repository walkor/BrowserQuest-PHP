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
class Character extends Entity
{
    public $orientation = null;
    public $attackers = array();
    public $target = null;
    public $maxHitPoints = 100;
    public $hitPoints = 10;
    
    public function __construct($id, $type, $kind, $x, $y)
    {
        parent::__construct($id, $type, $kind, $x, $y);
        
        $this->orientation = Utils::randomOrientation();
    }
    
    public function getState()
    {
        $basestate = $this->_getBaseState();
        $state = array();
        
        $state[] = $this->orientation;
        if($this->target)
        {
            $state[] = $this->target;
        }
        
        return array_merge($basestate, $state);
    }
    
    public function resetHitPoints($max_hit_points)
    {
        $this->maxHitPoints = $max_hit_points;
        $this->hitPoints = $this->maxHitPoints;
    }
    
    public function regenHealthBy($value)
    {
        $hp = $this->hitPoints;
        $max = $this->maxHitPoints;
        
        if($hp < $max) 
        {
            if($hp + $value <= $max) 
            {
                $this->hitPoints += $value;
            }
            else 
            {
                $this->hitPoints = $max;
            }
        }
    }
    
    public function hasFullHealth()
    {
        return $this->hitPoints == $this->maxHitPoints;
    }
    
    public function setTarget($entity) 
    {
        $this->target = $entity->id;
    }
    
    public function clearTarget()
    {
        $this->target = null;
    }
    
    public function hasTarget()
    {
        return $this->target !== null;
    }
    
    public function attack()
    {
        return new Messages\Attack($this->id, $this->target);
    }
    
    public function health()
    {
        return new Messages\Health($this->hitPoints, false);
    }
    
    public function regen()
    {
        return new Messages\Health($this->hitPoints, true);
    }
    
    public function addAttacker($entity)
    {
        if($entity)
        {
            $this->attackers[$entity->id] = $entity;
        }
    }
    
    public function removeAttacker($entity)
    {
         if($entity)
         {
            unset($this->attackers[$entity->id]);
        }
    }
    
    public function forEachAttacker($callback)
    {
        foreach($this->attackers as $id=>$item)
        {
            call_user_func($callback, $item);
        }
    }
}