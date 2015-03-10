<?php 
namespace Server\Messages;

use Server\Entity;

class Health
{
    /**
     * @var Entity
     */
    public $points   = 0;
    public $isRegen = 0;
    public function __construct($points, $is_regen)
    {
        $this->points = $points;
        $this->isRegen = $is_regen ;
    }
    
    public function serialize()
    {
        $health = array(TYPES_MESSAGES_HEALTH, $this->points);
        
        if($this->isRegen) 
        {
            $health[] =1;
        }
        return health;
    }
}

