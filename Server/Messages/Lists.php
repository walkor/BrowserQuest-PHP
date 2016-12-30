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

