<?php 
namespace Server;

class ChestArea extends Area
{
    public $items = null;
    public $chestX = 0;
    public $chestY = 0;
    public function __construct($id, $x, $y, $width, $height, $cx, $cy, $items, $world)
    {
        parent::__construct($id, $x, $y, $width, $height, $world);
        $this->items = $items;
        $this->chestX = $cx;
        $this->chestY = $cy;
    }
    public function contains($entity)
    {
        if($entity) {
            return $entity->x >= $this->x
            && $entity->y >= $this->y
            && $entity->x < $this->x + $this->width
            && $entity->y < $this->y + $this->height;
        } else {
            return false;
        }
    }
}