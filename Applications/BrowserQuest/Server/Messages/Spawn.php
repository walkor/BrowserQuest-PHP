<?php 
namespace Server\Messages;

class Spawn
{
    public $entity = null;
    public function __construct($entity)
    {
        $this->entity = $entity;
    }
    
    public function serialize()
    {
        $spawn = array(TYPES_MESSAGES_SPAWN);
        return array_merge($spawn, $this->entity->getState());
    }
}

