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

class LootMove
{
    /**
     * @var Entity
     */
    public $entity = null;
    /**
     * @var Item
     */
    public $item = null;
    public function __construct($entity, $item)
    {
        $this->entity = $entity;
        $this->item = $item;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_LOOTMOVE,
                $this->entity->id,
                $this->item->id,
        );
    }
}

