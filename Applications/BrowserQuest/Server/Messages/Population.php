<?php 
namespace Server\Messages;

class Population
{
    public $world = 0;
    public $total = 0;
    public function __construct($world, $total)
    {
        $this->world = $world;
        $this->total = $total;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_POPULATION, 
                $this->world,
                $this->total, 
        );
    }
}

