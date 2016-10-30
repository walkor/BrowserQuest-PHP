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

class Chest extends Item
{
    public $items = null;
    public function __construct($id, $x, $y)
    {
        parent::__construct($id, TYPES_ENTITIES_CHEST, $x, $y);
    }
    public function setItems($items)
    {
        $this->items = $items;
    }
    public function getRandomItem()
    {
        $item = null;
        if($this->items) 
        {
            return $this->items[array_rand($this->items)];
        }
    }
}