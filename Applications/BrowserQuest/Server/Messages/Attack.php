<?php 
namespace Server\Messages;

use Server\Entity;

class Attack
{
    /**
     * @var Entity
     */
    public $attackerId  = 0;
    public $targetId = 0;
    public function __construct($attacker_id, $target_id)
    {
        $this->attackerId = $attacker_id;
        $this->targetId = $target_id;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_ATTACK,
                $this->attackerId,
                $this->targetId,
        );
    }
}

