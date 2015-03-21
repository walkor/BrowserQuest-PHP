<?php 
namespace Server;

class Npc extends Entity
{
    public function __construct($id, $kind, $x, $y)
    {
        parent::__construct($id, 'npc', $kind, $x, $y);
    }
}