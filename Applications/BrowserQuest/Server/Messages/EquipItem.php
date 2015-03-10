<?php 
namespace Server\Messages;

use Server\Entity;

class EquipItem
{
    /**
     * @var Entity
     */
    public $playerId = 0;
    public $itemKind = 0;
    public function __construct($player, $item_kind)
    {
        $this->playerId = $player->id;
        $this->itemKind = $item_kind ;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_EQUIP, $this->playerId, $this->itemKind);
    }
}

