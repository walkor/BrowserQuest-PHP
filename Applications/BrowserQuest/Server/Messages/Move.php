<?php 
namespace Server\Messages;

use Server\Entity;

class Move
{
    /**
     * @var Entity
     */
    public $entity = null;
    public function __construct($entity)
    {
        $this->entity = $entity;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_MOVE,
                $this->entity->id,
                $this->entity->x,
                $this->entity->y,
        );
    }
}

