<?php 
namespace Server\Messages;

class Lists
{
    public $ids = null;
    public function __construct($ids)
    {
        $this->ids = $ids;
    }
    
    public function serialize()
    {
        $list = $this->ids;
        array_unshift($list, TYPES_MESSAGES_LIST);
        return $list;
    }
}

