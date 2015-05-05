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

use Server\Entity;

class EquipItem
{
    /**
     * @var Entity
     */
    public $playerId = 0;
    public $itemKind = 0;
    public function __construct($player, $item_kind)
    {
        $this->playerId = $player->id;
        $this->itemKind = $item_kind ;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_EQUIP, $this->playerId, $this->itemKind);
    }
}

