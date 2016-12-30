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

class Attack
{
    /**
     * @var Entity
     */
    public $attackerId  = 0;
    public $targetId = 0;
    public function __construct($attacker_id, $target_id)
    {
        $this->attackerId = $attacker_id;
        $this->targetId = $target_id;
    }
    
    public function serialize()
    {
        return array(TYPES_MESSAGES_ATTACK,
                $this->attackerId,
                $this->targetId,
        );
    }
}

