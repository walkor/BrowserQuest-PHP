<?php 
namespace Server\Messages;

class Damage
{
    public $entity = 0;
    public $points = 0;
    public function __construct($entity, $points)
    {
        $this->entity = $entity;
        $this->points = $points;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_DAMAGE, 
                $this->entity->id,
                $this->points, 
        );
    }
}

