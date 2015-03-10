<?php 
namespace Server;

class Checkpoint
{
    public $id = false;
    public $x = false;
    public $y = 0;
    public $width = 0;
    public $height = 0;
    public function __construct($id, $x, $y, $width, $height)
    {
        $this->id = $id;
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }
    
    public function getRandomPosition()
    {
        return array(
                'x'=>$this->x + rand(0, $this->width-1),
                'y'=>$this->y + rand(0, $this->height -1)
        );
    }
  
}
