<?php 
namespace Server;

class Npc extends Entity
{
    public function __construct($id, $type, $x, $y)
    {
        parent::__construct($id, $type, 'npc', $x, $y);
    }
}