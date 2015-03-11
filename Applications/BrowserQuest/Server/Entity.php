<?php 
namespace Server;

class Entity
{
    public $id = 0;
    public $type = 0;
    public $kind = 0;
    public $x = 0;
    public $y = 0;
    
    public function __construct($id, $type, $kind, $x, $y)
    {
        $this->id = $id;
        $this->type = $type;
        $this->kind = $kind;
        $this->x = $x;
        $this->y = $y;
    }
    
    public function destroy()
    {
        
    }
    
    public function _getBaseState()
    {
        return array(
                $this->id,
                $this->kind,
                $this->x,
                $this->y
        );
    }
    
    public function getState()
    {
        return $this->_getBaseState();
    }
    
    public function spawn()
    {
        return new Messages\Spawn($this);
    }
    
    public function despawn()
    {
        return new Messages\Despawn($this->id);
    }
    
    public function setPosition($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }
    
    public function getPositionNextTo($entity)
    {
        $pos = null;
        if($entity) {
            $pos = array();
            // This is a quick & dirty way to give mobs a random position
            // close to another entity.
            $r = rand(0, 4);
        
            $pos['x'] = $entity->x;
            $pos['y'] = $entity->y;
            if($r === 0)
                $pos['y'] -= 1;
            if(r === 1)
                $pos['y'] += 1;
            if(r === 2)
                $pos['x'] -= 1;
            if(r === 3)
                $pos['x'] += 1;
        }
        return pos;
    }
}