<?php 
namespace Server\Messages;

class Destroy
{
    public $item = null;
    public function __construct($item)
    {
        $this->item = $item;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_BLINK, 
                $this->item->id,
        );
    }
}

