<?php 
namespace Server\Messages;

use Server\Utils;

class Teleport
{
    public $entity = 0;
    public function __construct($entity)
    {
        $this->entity = $entity;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_TELEPORT, 
                $this->entity->id,
                $this->entity->x, 
                $this->entity->y
        );
    }
}

