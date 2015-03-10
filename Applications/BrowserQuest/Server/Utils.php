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
}