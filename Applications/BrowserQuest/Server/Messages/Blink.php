<?php 
namespace Server\Messages;

class Destroy
{
    public $entity = null;
    public function __construct($entity)
    {
        $this->entity = $entity;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_DESTROY, 
                $this->entity->id,
        );
    }
}

