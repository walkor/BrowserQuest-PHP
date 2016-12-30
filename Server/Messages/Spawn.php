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

class Spawn
{
    public $entity = null;
    public function __construct($entity)
    {
        $this->entity = $entity;
    }
    
    public function serialize()
    {
        $spawn = array(TYPES_MESSAGES_SPAWN);
        return array_merge($spawn, $this->entity->getState());
    }
}

