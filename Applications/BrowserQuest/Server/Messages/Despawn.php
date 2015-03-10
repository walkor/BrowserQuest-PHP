<?php 
namespace Server\Messages;

class Despawn
{
    public $entityId = 0;
    public function __construct($entity_id)
    {
        $this->entityId  = $entity_id;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_DESPAWN, $this->entityId);
    }
}

