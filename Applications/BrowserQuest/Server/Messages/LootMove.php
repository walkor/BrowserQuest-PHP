<?php 
namespace Server\Messages;

use Server\Entity;

class LootMove
{
    /**
     * @var Entity
     */
    public $entity = null;
    /**
     * @var Item
     */
    public $item = null;
    public function __construct($entity, $item)
    {
        $this->entity = $entity;
        $this->item = $item;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_LOOTMOVE,
                $this->entity->id,
                $this->item->id,
        );
    }
}

