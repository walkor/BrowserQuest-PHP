<?php 
namespace Server\Messages;

use Server\Entity;

class HitPoints
{
    /**
     * @var Entity
     */
    public $maxHitPoints    = 0;
    public function __construct($max_hit_points)
    {
        $this->maxHitPoints = $max_hit_points;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_HP, $this->maxHitPoints);
    }
}

