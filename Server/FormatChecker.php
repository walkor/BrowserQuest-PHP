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
class FormatChecker
{
    public $formats = array();
    public function __construct()
    {
            $this->formats[TYPES_MESSAGES_HELLO] = array('s', 'n', 'n');
            $this->formats[TYPES_MESSAGES_MOVE] = array('n', 'n');
            $this->formats[TYPES_MESSAGES_LOOTMOVE] = array('n', 'n', 'n');
            $this->formats[TYPES_MESSAGES_AGGRO] = array('n');
            $this->formats[TYPES_MESSAGES_ATTACK] = array('n');
            $this->formats[TYPES_MESSAGES_HIT] = array('n');
            $this->formats[TYPES_MESSAGES_HURT] = array('n');
            $this->formats[TYPES_MESSAGES_CHAT] = array('s');
            $this->formats[TYPES_MESSAGES_LOOT] = array('n');
            $this->formats[TYPES_MESSAGES_TELEPORT] = array('n', 'n');
            $this->formats[TYPES_MESSAGES_ZONE] = array();
            $this->formats[TYPES_MESSAGES_OPEN] = array('n');
            $this->formats[TYPES_MESSAGES_CHECK] = array('n');
        }
    
        public function check($msg) 
        {
            $message = $msg[0];
            $type = $message[0];
            $format = $this->formats[$type];
    
            array_shift($message);
    
        }
}