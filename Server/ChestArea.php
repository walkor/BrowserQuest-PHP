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

class ChestArea extends Area
{
    public $items = null;
    public $chestX = 0;
    public $chestY = 0;
    public function __construct($id, $x, $y, $width, $height, $cx, $cy, $items, $world)
    {
        parent::__construct($id, $x, $y, $width, $height, $world);
        $this->items = $items;
        $this->chestX = $cx;
        $this->chestY = $cy;
    }
    public function contains($entity)
    {
        if($entity) 
        {
            return $entity->x >= $this->x
            && $entity->y >= $this->y
            && $entity->x < $this->x + $this->width
            && $entity->y < $this->y + $this->height;
        } else {
            return false;
        }
    }
}