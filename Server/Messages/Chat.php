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

use Server\Utils;

class Chat
{
    public $playerId = 0;
    public $message = null;
    public function __construct($player, $message)
    {
        $this->playerId = $player->id;
        $this->message = $message ;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_CHAT, 
                $this->playerId, 
                $this->message
        );
    }
}

