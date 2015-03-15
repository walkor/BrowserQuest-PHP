<?php 
namespace Server;

class Map
{
    public $isLoaded;
    public $width;
    public $height;
    public $collisions;
    public $mobAreas;
    public $chestAreas;
    public $staticChests;
    public $staticEntities;
    public $zoneWidth;
    public $zoneHeight;
    public $groupWidth;
    public $groupHeight;
    public $readyFunc;
    
    public $grid;
    
    public function __construct($filepath)
    {
        $file = __DIR__.'/../'.$filepath;
        if(!file_exists($file))
        {
            echo "$filepath  doesn't exist.";
        }
        $json = json_decode(file_get_contents($file));
        $this->initMap($json);
    }
    
    public function initMap($map)
    {
        $this->width = $map->width;
        $this->height = $map->height;
        $this->collisions = $map->collisions;
        $this->mobAreas = $map->roamingAreas;
        $this->chestAreas = $map->chestAreas;
        $this->staticChests = $map->staticChests;
        $this->staticEntities = $map->staticEntities;
        $this->isLoaded = true;
        
        // zone groups
        $this->zoneWidth = 28;
        $this->zoneHeight = 12;
        $this->groupWidth = floor($this->width / $this->zoneWidth);
        $this->groupHeight = floor($this->height / $this->zoneHeight);
        
        $this->initConnectedGroups($map->doors);
        $this->initCheckpoints($map->checkpoints);
        
        if($this->readyFunc)
        {
            call_user_func($this->readyFunc);
        }
    }
    
    public function ready($callback)
    {
        $this->readyFunc = $callback;
    }
    
    public function tile_indexToGridPosition($tile_num)
    {
        $x = 0;
        $y = 0;
        
        $tile_num -= 1;
        $x = $this->getX($tile_num + 1, $this->width);
        $y = floor($tile_num / $this->width);
        
        return array('x'=> $x, 'y'=> $y);
    }
    
    protected function getX($num, $w) 
    {
        if($num == 0) 
        {
            return 0;
        }
        return ($num % $w == 0) ? $w - 1 : ($num % $w) - 1;
    }
    
    public function GridPositionTotile_index($x ,$y)
    {
        return ($y * $this->width) + $x + 1;
    }
    
    public function generateCollisionGrid()
    {
        $this->grid = array();
        
        if($this->isLoaded) 
        {
            $tile_index = 0;
            for($i = 0; $i < $this->height; $i++) 
            {
                $this->grid[$i] = array();
                for($j = 0; $j < $this->width; $j++) 
                {
                    // @todo use isset 
                    if(in_array($tile_index, $this->collisions)) 
                    {
                        $this->grid[$i][$j] = 1;
                    }
                    else 
                    {
                        $this->grid[$i][$j] = 0;
                    }
                    $tile_index += 1;
                }
            }
        }
    }
    
    public function isOutOfBounds($x, $y)
    {
        return $x <= 0 || $x >= $this->width || $y <= 0 || $y >= $this->height;
    }
    
    public function isColliding($x, $y)
    {
        if($this->isOutOfBounds($x, $y)) 
        {
            return false;
        }
        return $this->grid[$y][$x] === 1;
    }
    
    public function group_idToGroupPosition($id)
    {
        $pos_array =explode('-', $id);
        
        return array('x'=>(int)$pos_array[0], 'y'=>(int)($pos_array[1]));
    }
    
    public function forEachGroup($callback)
    {
        $width = $this->groupWidth;
        $height = $this->groupHeight;
        
        for($x = 0; $x < $width; $x += 1) 
        {
            for($y = 0; $y < $height; $y += 1) 
            {
                call_user_func($x.'-'.$y);
            }
        }
    }
    
    public function getgroup_idFromPosition($x ,$y)
    {
        $w = $this->zoneWidth;
        $h = $this->zoneHeight;
        $gx = floor(($x - 1) / $w);
        $gy = floor(($y - 1) / $h);
        return $gx.'-'.$gy;
    }
    
    public function getAdjacentGroupPositions($id)
    {
        $position = $this->group_idToGroupPosition(id);
        $x = $position['x'];
        $y = $position['y'];
        // surrounding groups
        $list = array(array('x'=>$x-1, 'y'=>$y-1), array('x'=>$x, 'y'=>$y-1), array('x'=>$x+1, 'y'=>$y-1),
        array('x'=>$x-1, 'y'=>$y), array('x'=>$x, $y), array($x+1, $y),
        array('x'=>$x-1, 'y'=>$y+1), array('x'=>$x, 'y'=>y+1), array('x'=>$x+1, 'y'=>$y+1));
        
        // groups connected via doors
        array_walk($this->connectedGroups[$id], function ($key, $position){
            // don't add a connected group if it's already part of the surrounding ones.
            if(Utils::any($list, function($group_pos) {
                return $this->equalPositions($group_pos, $position);
            })) 
            {
                $list[] = $position;
            }
        });
        
        return Utils::reject($list, function($pos) 
        {
            return $pos['x'] < 0 || $pos['y'] < 0 || $pos['x'] >= $this->groupWidth || $pos['y'] >= $this->groupHeight;
        });
    }
    
    public function forEachAdjacentGroup($group_id, $callback)
    {
        if($group_id) {
            array_walk($this->getAdjacentGroupPositions($group_id), function($pos) 
            {
                call_user_func($callback, $pos['x'].'-'.$pos['y']);
            });
        }
    }
    
    public function initConnectedGroups($doors)
    {
        $this->connectedGroups = array();
        $self = $this;
        array_walk($doors, function($door)use($self)
        {
            $group_id = $self->getgroup_idFromPosition($door->x, $door->y);
            $connectedgroup_id = $self->getgroup_idFromPosition($door->tx, $door->ty);
            $connectedPosition = $self->group_idToGroupPosition($connectedgroup_id);
        
            if(isset($self->connectedGroups[$group_id])) 
            {
                $self->connectedGroups[$group_id][] =$connectedPosition;
            } 
            else 
            {
                $self->connectedGroups[$group_id] = array($connectedPosition);
            }
        });
    }
    
    public function initCheckpoints($cpList)
    {
        $this->checkpoints = array();
        $this->startingAreas = array();
        $self = $this;
        array_walk($cpList, function($cp)use($self) 
        {
            $checkpoint = new Checkpoint($cp->id, $cp->x, $cp->y, $cp->w, $cp->h);
            $self->checkpoints[$checkpoint->id] = $checkpoint;
            if($cp->s === 1) 
            {
                $self->startingAreas[] = $checkpoint;
            }
        });
    }
    
    public function getCheckpoint($id)
    {
        return $this->checkpoints[$id];
    }
    
    public function getRandomStartingPosition()
    {
        $nbAreas = count($this->startingAreas);
        $i = rand(0, $nbAreas-1);
        $area = $this->startingAreas[$i];
        
        return $area->getRandomPosition();
    }
    
    protected function equalPositions($pos1, $pos2)
    {
        return $pos1['x'] === $pos2['x'] && $pos2['y'] === $pos2['y'];
    }
}
