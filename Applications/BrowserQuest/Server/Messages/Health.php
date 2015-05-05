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

class Health
{
    /**
     * @var Entity
     */
    public $points   = 0;
    public $isRegen = 0;
    public function __construct($points, $is_regen)
    {
        $this->points = $points;
        $this->isRegen = $is_regen ;
    }
    
    public function serialize()
    {
        $health = array(TYPES_MESSAGES_HEALTH, $this->points);
        
        if($this->isRegen) 
        {
            $health[] =1;
        }
        return $health;
    }
}

