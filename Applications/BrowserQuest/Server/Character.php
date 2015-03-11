<?php
namespace Server;
class Character
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
    
            if($format) {
                if(message.length !== format.length) {
                    return false;
                }
                for(var i = 0, n = message.length; i < n; i += 1) {
                    if(format[i] === 'n' && !_.isNumber(message[i])) {
                        return false;
                    }
                    if(format[i] === 's' && !_.isString(message[i])) {
                        return false;
                    }
                }
                return true;
            }
            else if(type === TYPES_MESSAGES_WHO) {
                // WHO messages have a variable amount of params, all of which must be numbers.
                return message.length > 0 && _.all(message, function(param) {
                    return _.isNumber(param) });
            }
            else {
                log.error("Unknown message type: "+type);
                return false;
            }
        }
}