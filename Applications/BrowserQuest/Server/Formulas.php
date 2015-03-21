<?php
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