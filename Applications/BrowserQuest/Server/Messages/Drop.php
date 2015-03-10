<?php 
namespace Server\Messages;

use Server\Utils;

class Drop
{
    public $mob = 0;
    public $item = 0;
    public function __construct($mob, $item)
    {
        $this->playerId = $mob;
        $this->itemKind = $item ;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_DROP, 
                $this->mob->id, 
                $this->item->id,
                $this->item->kind,
                Utils::pluck($this->mob->hatelist, 'id')
        );
    }
}

