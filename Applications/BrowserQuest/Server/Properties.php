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

class Properties
{
    public static $properties = array(
            'rat'=> array(
                'drops'=> array(
                    'flask'=> 40,
                    'burger'=> 10,
                    'firepotion'=> 5
                ),
                'hp'=> 25,
                'armor'=> 1,
                'weapon'=> 1
            ),
            
            'skeleton'=> array(
                'drops'=> array(
                    'flask'=> 40,
                    'mailarmor'=> 10,
                    'axe'=> 20,
                    'firepotion'=> 5
                ),
                'hp'=> 110,
                'armor'=> 2,
                'weapon'=> 2
            ),
            
            'goblin'=> array(
                'drops'=> array(
                    'flask'=> 50,
                    'leatherarmor'=> 20,
                    'axe'=> 10,
                    'firepotion'=> 5
                ),
                'hp'=> 90,
                'armor'=> 2,
                'weapon'=> 1
            ),
            
            'ogre'=> array(
                'drops'=> array(
                    'burger'=> 10,
                    'flask'=> 50,
                    'platearmor'=> 20,
                    'morningstar'=> 20,
                    'firepotion'=> 5
                ),
                'hp'=> 200,
                'armor'=> 3,
                'weapon'=> 2
            ),
            
            'spectre'=> array(
                'drops'=> array(
                    'flask'=> 30,
                    'redarmor'=> 40,
                    'redsword'=> 30,
                    'firepotion'=> 5
                ),
                'hp'=> 250,
                'armor'=> 2,
                'weapon'=> 4
            ),
            
            'deathknight'=> array(
                'drops'=> array(
                    'burger'=> 95,
                    'firepotion'=> 5
                ),
                'hp'=> 250,
                'armor'=> 3,
                'weapon'=> 3
            ),
            
            'crab'=> array(
                'drops'=> array(
                    'flask'=> 50,
                    'axe'=> 20,
                    'leatherarmor'=> 10,
                    'firepotion'=> 5
                ),
                'hp'=> 60,
                'armor'=> 2,
                'weapon'=> 1
            ),
            
            'snake'=> array(
                'drops'=> array(
                    'flask'=> 50,
                    'mailarmor'=> 10,
                    'morningstar'=> 10,
                    'firepotion'=> 5
                ),
                'hp'=> 150,
                'armor'=> 3,
                'weapon'=> 2
            ),
            
            'skeleton2'=> array(
                'drops'=> array(
                    'flask'=> 60,
                    'platearmor'=> 15,
                    'bluesword'=> 15,
                    'firepotion'=> 5
                ),
                'hp'=> 200,
                'armor'=> 3,
                'weapon'=> 3
            ),
            
            'eye'=> array(
                'drops'=> array(
                    'flask'=> 50,
                    'redarmor'=> 20,
                    'redsword'=> 10,
                    'firepotion'=> 5
                ),
                'hp'=> 200,
                'armor'=> 3,
                'weapon'=> 3
            ),
            
            'bat'=> array(
                'drops'=> array(
                    'flask'=> 50,
                    'axe'=> 10,
                    'firepotion'=> 5
                ),
                'hp'=> 80,
                'armor'=> 2,
                'weapon'=> 1
            ),
            
            'wizard'=> array(
                'drops'=> array(
                    'flask'=> 50,
                    'platearmor'=> 20,
                    'firepotion'=> 5
                ),
                'hp'=> 100,
                'armor'=> 2,
                'weapon'=> 6
            ),
            
            'boss'=> array(
                'drops'=> array(
                    'goldensword'=> 100
                ),
                'hp'=> 700,
                'armor'=> 6,
                'weapon'=> 7
            )
     );
    
    public static function getArmorLevel($kind)
    {
        if(Types::isMob($kind))
        {
            return self::$properties[Types::getKindAsString($kind)]['armor'];
        }
        return Types::getArmorRank($kind)+1;
    }
    
    public static function getWeaponLevel($kind)
    {
        if(Types::isMob($kind))
        {
            return self::$properties[Types::getKindAsString($kind)]['weapon'];
        }
        return Types::getWeaponRank($kind)+1;
    }
    
    public static function getHitPoints($kind)
    {
        return self::$properties[Types::getKindAsString($kind)]['hp'];
    }
}

