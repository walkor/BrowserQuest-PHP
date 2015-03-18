<?php 
namespace Server;

class Area
{
    public $id = 0;
    public $x = 0;
    public $y = 0;
    public $width = 0;
    public $height = 0;
    /**
     * @var WorldServer
     */
    public $world = null;
    public $entities = array();
    public $hasCompletelyRespawned = false;
    public $nbEntities = 2;
    
    public $emptyCallback = null;
    
    public function __construct($id, $x, $y, $width, $height, $world)
    {
        $this->id = $id;
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
        $this->world = $world;
    }
    
    public function _getRandomPositionInsideArea()
    {
        $pos = array();
        $valid = false;
        
        while(!$valid) {
            $pos['x'] = $this->x + rand(0, $this->width + 1);
            $pos['y'] = $this->y + rand(0, $this->height + 1);
            $valid = $this->world->isValidPosition($pos['x'], $pos['y']);
        }
        return $pos;
    }
    
    public function removeFromArea($entity)
    {
        $index = array_search(Utils::pluck($this->entities, 'id'), $entity->id);
        unset($this->entities[$index]);
    }
    
    public function addToArea($entity)
    {
        if($entity)
        {
            $this->entities[] = $entity;
            $entity->area = $this;
            if($entity instanceof Mob)
            {
                $this->world->addMob($entity);
            }
        }
        if($this->isFull())
        {
            $this->hasCompletelyRespawned = true;
        }
    }
    
    public function setNumberOfEntities($nb)
    {
        $this->nbEntities = $nb;
    }
    
    public function isFull()
    {
        return !$this->isEmpty() && ($this->nbEntities == count($this->entities));
    }
    
    public function isEmpty()
    {
        foreach ($this->entities as $entity)
        {
            if(!$entity->isDead)
            {
                return false;
            }
        }
        return true;
    }
    
    public function onEmpty($callback)
    {
        $this->emptyCallback = $callback;
    }
}