<?php 
namespace Server;

class Entity
{
    public $id = 0;
    public $type = 0;
    public $kind = 0;
    public $x = 0;
    public $y = 0;
    
    public function __construct($id, $type, $kind, $x, $y)
    {
        $this->id = $id;
        $this->type = $type;
        $this->kind = $kind;
        $this->x = $x;
        $this->y = $y;
    }
    
    public function destroy()
    {
        
    }
    
    public function _getBaseState()
    {
        return array(
                $this->id,
                $this->kind,
                $this->x,
                $this->y
        );
    }
    
    public function getState()
    {
        return $this->_getBaseState();
    }
    
    public function spawn()
    {
        
    }
}