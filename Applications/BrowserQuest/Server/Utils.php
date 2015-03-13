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
        return $item;
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
}