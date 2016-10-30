<?php 
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
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
    public $connectedGroups = array();
    public $grid;
    public $filePath;
    
    public function __construct($filepath)
    {
        $this->filePath = $filepath;
        $this->isLoaded = false;
    }
    
    public function initMap()
    {
        $file = __DIR__.'/../'.$this->filePath;
        if(!file_exists($file))
        {
            echo "$file  doesn't exist.\n";
        }
        $map = json_decode(file_get_contents($file));
        
        $this->width = $map->width;
        $this->height = $map->height;
        $this->collisions = (array)$map->collisions;
        $this->mobAreas = (array)$map->roamingAreas;
        $this->chestAreas = (array)$map->chestAreas;
        $this->staticChests =(array) $map->staticChests;
        $this->staticEntities = (array)$map->staticEntities;
        $this->isLoaded = true;
        
        // ??
        foreach($this->chestAreas as $id=>$item)
        {
            $this->chestAreas[$id]->id = $id;
        }
        
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
    
    public function titleIndexToGridPosition($tile_num)
    {
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
    
    public function GridPositionToTileIndex($x ,$y)
    {
        return ($y * $this->width) + $x + 1;
    }
    
    public function generateCollisionGrid()
    {
        $this->grid = array();
        
        if($this->isLoaded) 
        {
            $tile_index = 0;
            $collisions_map = array_flip($this->collisions);
            for($i = 0; $i < $this->height; $i++) 
            {
                $this->grid[$i] = array();
                for($j = 0; $j < $this->width; $j++) 
                {
                    // @todo use isset 
                    if(isset($collisions_map[$tile_index])) 
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
        return $this->grid[$y][$x] == 1;
    }
    
    public function GroupIdToGroupPosition($id)
    {
        $pos_array =explode('-', $id);
        
        return array('x'=>$pos_array[0], 'y'=>$pos_array[1]);
    }
    
    public function forEachGroup($callback)
    {
        $width = $this->groupWidth;
        $height = $this->groupHeight;
        
        for($x = 0; $x < $width; $x += 1) 
        {
            for($y = 0; $y < $height; $y += 1) 
            {
                call_user_func($callback, $x.'-'.$y);
            }
        }
    }
    
    public function getGroupIdFromPosition($x ,$y)
    {
        $w = $this->zoneWidth;
        $h = $this->zoneHeight;
        $gx = floor(($x - 1) / $w);
        $gy = floor(($y - 1) / $h);
        return $gx.'-'.$gy;
    }
    
    public function getAdjacentGroupPositions($id)
    {
        $position = $this->GroupIdToGroupPosition($id);
        $x = $position['x'];
        $y = $position['y'];
        // surrounding groups
        $list = array(array('x'=>$x-1, 'y'=>$y-1), array('x'=>$x, 'y'=>$y-1), array('x'=>$x+1, 'y'=>$y-1),
        array('x'=>$x-1, 'y'=>$y), array('x'=>$x, 'y'=>$y), array('x'=>$x+1, 'y'=>$y),
        array('x'=>$x-1, 'y'=>$y+1), array('x'=>$x, 'y'=>$y+1), array('x'=>$x+1, 'y'=>$y+1));
        
        // groups connected via doors
        $self = $this;
        if(!empty($this->connectedGroups[$id]))
        {
            array_walk($this->connectedGroups[$id], function ($position) use (&$list, $self){
                // don't add a connected group if it's already part of the surrounding ones.
                if(Utils::any($list, function($group_pos)use($position, $self) {
                    return $self->equalPositions($group_pos, $position);
                })) 
                {
                    $list[] = $position;
                }
            });
        }
        
        return Utils::reject($list, function($pos)use($self) 
        {
            return $pos['x'] < 0 || $pos['y'] < 0 || $pos['x'] >= $self->groupWidth || $pos['y'] >= $self->groupHeight;
        });
    }
    
    public function forEachAdjacentGroup($group_id, $callback)
    {
        if($group_id) 
        {
            $groups = $this->getAdjacentGroupPositions($group_id);
            array_walk($groups, function($pos)use($callback) 
            {
                call_user_func($callback, $pos['x'].'-'.$pos['y']);
            });
        }
    }
    
    public function initConnectedGroups($doors)
    {
        $self = $this;
        array_walk($doors, function($door)use($self)
        {
            $group_id = $self->getGroupIdFromPosition($door->x, $door->y);
            $connectedgroup_id = $self->getGroupIdFromPosition($door->tx, $door->ty);
            $connectedPosition = $self->GroupIdToGroupPosition($connectedgroup_id);
        
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
            if($cp->s == 1) 
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
    
    public function equalPositions($pos1, $pos2)
    {
        return $pos1['x'] == $pos2['x'] && $pos2['y'] == $pos2['y'];
    }
}
