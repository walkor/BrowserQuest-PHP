<?php 
namespace Server;

class Chest extends Item
{
    public $items = null;
    public function __construct($id, $x, $y)
    {
        parent::__construct($id, TYPES_ENTITIES_CHEST, $x, $y);
    }
    public function setItems($items)
    {
        $this->items = items;
    }
    public function getRandomItem()
    {
        $item = null;
        if(count($this->items) > 0) {
            $item = $this->items[array_rand($this->items)];
        }
        return item;
    }
}