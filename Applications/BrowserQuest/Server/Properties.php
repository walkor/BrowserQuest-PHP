<?php 
namespace Server;

class Properties
{
    public $properties = array(
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
        
    }
}


Properties.getArmorLevel = function(kind) {
    try {
        if(Types.isMob(kind)) {
            return Properties[Types.getKindAsString(kind)].armor;
        } else {
            return Types.getArmorRank(kind) + 1;
        }
    } catch(e) {
        log.error("No level found for armor: "+Types.getKindAsString(kind));
    }
};

Properties.getWeaponLevel = function(kind) {
    try {
        if(Types.isMob(kind)) {
            return Properties[Types.getKindAsString(kind)].weapon;
        } else {
            return Types.getWeaponRank(kind) + 1;
        }
    } catch(e) {
        log.error("No level found for weapon: "+Types.getKindAsString(kind));
    }
};

Properties.getHitPoints = function(kind) {
    return Properties[Types.getKindAsString(kind)].hp;
};

module.exports = Properties;