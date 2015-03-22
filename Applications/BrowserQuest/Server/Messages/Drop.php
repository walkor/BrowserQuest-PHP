<?php 
namespace Server\Messages;

use Server\Utils;

class Drop
{
    public $mob;
    public $item;
    public function __construct($mob, $item)
    {
        $this->mob = $mob;
        $this->item = $item ;
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

