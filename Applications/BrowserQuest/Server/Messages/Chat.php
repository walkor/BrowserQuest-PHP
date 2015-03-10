<?php 
namespace Server\Messages;

use Server\Utils;

class Chat
{
    public $playerId = 0;
    public $message = null;
    public function __construct($player, $message)
    {
        $this->playerId = $player->id;
        $this->message = $message ;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_CHAT, 
                $this->playerId, 
                $this->message
        );
    }
}

