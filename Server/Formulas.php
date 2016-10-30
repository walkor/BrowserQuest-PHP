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
class Formulas
{
    public static function dmg($weaponLevel, $armorLevel)
    {
        $dealt = $weaponLevel * rand(5, 10);
        $absorbed = $armorLevel * rand(1, 3);
        $dmg =  $dealt - $absorbed;
        
        if($dmg <= 0) {
            return rand(0, 3);
        } else {
            return $dmg;
        }
    }
    
    public static function hp($armorLevel)
    {
        $hp = 80 + (($armorLevel - 1) * 30);
        return $hp;
    }
}