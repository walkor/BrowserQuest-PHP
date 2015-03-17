<?php
namespace Server;

class Utils
{
    public static function pluck(array $list, $key)
    {
        $value_array = array();
        foreach($list as $item)
        {
            $value_array =$item[$key];
        }
        return $value_array;
    }
    
    public static function reject(array $list, $callback)
    {
        $arr = array();
        foreach($list as $item)
        {
            if(!call_user_func($callback, $item))
            {
                $arr[] = $item;
            }
        }
        return $arr;
    }
    
    public static function any(array $list, $callback)
    {
        foreach($list as $item)
        {
            if(call_user_func($callback, $item))
            {
                return true;
            }
        }
        return false;
    }
    
    public static function detect(array $list, $callback)
    {
        foreach($list as $item)
        {
            if(call_user_func($callback, $item))
            {
                return $item;
            }
        }
    }
    
    public static function sortBy(array $list, $callback)
    {
        usort($list, $callback);
        return $list;
    }
    
    public static function distanceTo($x, $y, $x2, $y2) 
    {
        $distX = abs($x - $x2);
        $distY = abs($y - $y2);
    
        return ($distX > $distY) ? $distX : $distY;
    }
    
    public static function randomOrientation() 
    {
        $r = rand(0, 4);
        if($r === 0)
            return TYPES_ORIENTATIONS_LEFT;
        if($r === 1)
            return TYPES_ORIENTATIONS_RIGHT;
        if($r === 2)
            return TYPES_ORIENTATIONS_UP;
        if($r === 3)
            return TYPES_ORIENTATIONS_DOWN;
    }
}
