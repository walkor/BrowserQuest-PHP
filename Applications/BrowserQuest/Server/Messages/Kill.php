<?php 
namespace Server\Messages;

class Kill
{
    public $mob = null;
    public function __construct($mob)
    {
        $this->mob = $mob;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_KILL, 
                $this->mob->kind,
        );
    }
}

