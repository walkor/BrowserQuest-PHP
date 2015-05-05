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
class Types
{
    public static $typesToString = array(
            'Messages' => array(
                0 => 'HELLO',
                1 => 'WELCOME',
                2 => 'SPAWN',
                3 => 'DESPAWN',
                4 => 'MOVE',
                5 => 'LOOTMOVE',
                6 => 'AGGRO',
                7 => 'ATTACK',
                8 => 'HIT',
                9 => 'HURT',
                10 => 'HEALTH',
                11 => 'CHAT',
                12 => 'LOOT',
                13 => 'EQUIP',
                14 => 'DROP',
                15 => 'TELEPORT',
                16 => 'DAMAGE',
                17 => 'POPULATION',
                18 => 'KILL',
                19 => 'LIST',
                20 => 'WHO',
                21 => 'ZONE',
                22 => 'DESTROY',
                23 => 'HP',
                24 => 'BLINK',
                25 => 'OPEN',
                26 => 'CHECK'
            ),
            
            'Entities' => array(
                1 => 'WARRIOR',
            
                // Mobs
                2 => 'RAT',
                3 => 'SKELETON',
                4 => 'GOBLIN',
                5 => 'OGRE',
                6 => 'SPECTRE',
                7 => 'CRAB',
                8 => 'BAT',
                9 => 'WIZARD',
                10 => 'EYE',
                11 => 'SNAKE',
                12 => 'SKELETON2',
                13 => 'BOSS',
                14 => 'DEATHKNIGHT',
            
                // Armors
                20 => 'FIREFOX',
                21 => 'CLOTHARMOR',
                22 => 'LEATHERARMOR',
                23 => 'MAILARMOR',
                24 => 'PLATEARMOR',
                25 => 'REDARMOR',
                26 => 'GOLDENARMOR',
            
                // Objects
                35 => 'FLASK',
                36 => 'BURGER',
                37 => 'CHEST',
                38 => 'FIREPOTION',
                39 => 'CAKE',
            
                // NPCs
                40 => 'GUARD',
                41 => 'KING',
                42 => 'OCTOCAT',
                43 => 'VILLAGEGIRL',
                44 => 'VILLAGER',
                45 => 'PRIEST',
                46 => 'SCIENTIST',
                47 => 'AGENT',
                48 => 'RICK',
                49 => 'NYAN',
                50 => 'SORCERER',
                51 => 'BEACHNPC',
                52 => 'FORESTNPC',
                53 => 'DESERTNPC',
                54 => 'LAVANPC',
                55 => 'CODER',
            
                // Weapons
                60 => 'SWORD1',
                61 => 'SWORD2',
                62 => 'REDSWORD',
                63 => 'GOLDENSWORD',
                64 => 'MORNINGSTAR',
                65 => 'AXE',
                66 => 'BLUESWORD'
            ),
            
            'Orientations' => array(
                1 => 'UP',
                2 => 'DOWN',
                3 => 'LEFT',
                4 => 'RIGHT'
            )
    );
    
    public static $stringToKindsMap = array(
            'warrior'=> array(TYPES_ENTITIES_WARRIOR, "player"),
            
            'rat'=> array(TYPES_ENTITIES_RAT, "mob"),
            'skeleton'=> array(TYPES_ENTITIES_SKELETON , "mob"),
            'goblin'=> array(TYPES_ENTITIES_GOBLIN, "mob"),
            'ogre'=> array(TYPES_ENTITIES_OGRE, "mob"),
            'spectre'=> array(TYPES_ENTITIES_SPECTRE, "mob"),
            'deathknight'=> array(TYPES_ENTITIES_DEATHKNIGHT, "mob"),
            'crab'=> array(TYPES_ENTITIES_CRAB, "mob"),
            'snake'=> array(TYPES_ENTITIES_SNAKE, "mob"),
            'bat'=> array(TYPES_ENTITIES_BAT, "mob"),
            'wizard'=> array(TYPES_ENTITIES_WIZARD, "mob"),
            'eye'=> array(TYPES_ENTITIES_EYE, "mob"),
            'skeleton2'=> array(TYPES_ENTITIES_SKELETON2, "mob"),
            'boss'=> array(TYPES_ENTITIES_BOSS, "mob"),
            
            'sword1'=> array(TYPES_ENTITIES_SWORD1, "weapon"),
            'sword2'=> array(TYPES_ENTITIES_SWORD2, "weapon"),
            'axe'=> array(TYPES_ENTITIES_AXE, "weapon"),
            'redsword'=> array(TYPES_ENTITIES_REDSWORD, "weapon"),
            'bluesword'=> array(TYPES_ENTITIES_BLUESWORD, "weapon"),
            'goldensword'=> array(TYPES_ENTITIES_GOLDENSWORD, "weapon"),
            'morningstar'=> array(TYPES_ENTITIES_MORNINGSTAR, "weapon"),
            
            'firefox'=> array(TYPES_ENTITIES_FIREFOX, "armor"),
            'clotharmor'=> array(TYPES_ENTITIES_CLOTHARMOR, "armor"),
            'leatherarmor'=> array(TYPES_ENTITIES_LEATHERARMOR, "armor"),
            'mailarmor'=> array(TYPES_ENTITIES_MAILARMOR, "armor"),
            'platearmor'=> array(TYPES_ENTITIES_PLATEARMOR, "armor"),
            'redarmor'=> array(TYPES_ENTITIES_REDARMOR, "armor"),
            'goldenarmor'=> array(TYPES_ENTITIES_GOLDENARMOR, "armor"),
            
            'flask'=> array(TYPES_ENTITIES_FLASK, "object"),
            'cake'=> array(TYPES_ENTITIES_CAKE, "object"),
            'burger'=> array(TYPES_ENTITIES_BURGER, "object"),
            'chest'=> array(TYPES_ENTITIES_CHEST, "object"),
            'firepotion'=> array(TYPES_ENTITIES_FIREPOTION, "object"),
            
            'guard'=> array(TYPES_ENTITIES_GUARD, "npc"),
            'villagegirl'=> array(TYPES_ENTITIES_VILLAGEGIRL, "npc"),
            'villager'=> array(TYPES_ENTITIES_VILLAGER, "npc"),
            'coder'=> array(TYPES_ENTITIES_CODER, "npc"),
            'scientist'=> array(TYPES_ENTITIES_SCIENTIST, "npc"),
            'priest'=> array(TYPES_ENTITIES_PRIEST, "npc"),
            'king'=> array(TYPES_ENTITIES_KING, "npc"),
            'rick'=> array(TYPES_ENTITIES_RICK, "npc"),
            'nyan'=> array(TYPES_ENTITIES_NYAN, "npc"),
            'sorcerer'=> array(TYPES_ENTITIES_SORCERER, "npc"),
            'agent'=> array(TYPES_ENTITIES_AGENT, "npc"),
            'octocat'=> array(TYPES_ENTITIES_OCTOCAT, "npc"),
            'beachnpc'=> array(TYPES_ENTITIES_BEACHNPC, "npc"),
            'forestnpc'=> array(TYPES_ENTITIES_FORESTNPC, "npc"),
            'desertnpc'=> array(TYPES_ENTITIES_DESERTNPC, "npc"),
            'lavanpc'=> array(TYPES_ENTITIES_LAVANPC, "npc"),
    );
    
    public static $rankedWeapons = array(
            TYPES_ENTITIES_SWORD1 => 0,
            TYPES_ENTITIES_SWORD2 => 1,
            TYPES_ENTITIES_AXE => 2,
            TYPES_ENTITIES_MORNINGSTAR => 3,
            TYPES_ENTITIES_BLUESWORD => 4,
            TYPES_ENTITIES_REDSWORD => 5,
            TYPES_ENTITIES_GOLDENSWORD => 6
    );
    
    public static $rankedArmors = array(
            TYPES_ENTITIES_CLOTHARMOR => 0,
            TYPES_ENTITIES_LEATHERARMOR => 1,
            TYPES_ENTITIES_MAILARMOR => 2,
            TYPES_ENTITIES_PLATEARMOR => 3,
            TYPES_ENTITIES_REDARMOR => 4,
            TYPES_ENTITIES_GOLDENARMOR =>5
    );
    
    protected static $kindsToStringMap = array();
    
    protected static $kindsToTypesMap = array();
    
    protected static $randomItemKind = array();
    
    protected static function getKindsToStringMap()
    {
        if(!self::$kindsToStringMap)
        {
            foreach(self::$stringToKindsMap as $string=>$item)
            {
                self::$kindsToStringMap[$item[0]] = $string;
            }
        }
        return self::$kindsToStringMap;
    }
    
    protected static function getKindsToTypesMap()
    {
        if(!self::$kindsToTypesMap)
        {
            foreach(self::$stringToKindsMap as $string=>$item)
            {
                self::$kindsToTypesMap[$item[0]] = $item[1];
            }
        }
        return self::$kindsToTypesMap;
    }
    
    public static function getType($kind)
    {
        $kinds_to_types_map = self::getKindsToTypesMap();
        return $kinds_to_types_map[$kind];
    }
    
    public static function getWeaponRank($weaponKind)
    {
        return self::$rankedWeapons[$weaponKind];
    }
    
    public static function getArmorRank($armorKind)
    {
        return self::$rankedArmors[$armorKind];
    }
    
    public static function isPlayer($kind)
    {
        return self::getType($kind) == 'player';
    }
    
    public static function isMob($kind)
    {
        return self::getType($kind) == 'mob';
    }
    
    public static function isNpc($kind)
    {
        return self::getType($kind) == 'npc';
    }
    
    public static function isCharacter($kind)
    {
        return (self::isMob($kind) || self::isNpc($kind) || self::isPlayer($kind)); 
    }
    
    public static function isArmor($kind)
    {
        return self::getType($kind) == 'armor';
    }
    
    public static function isWeapon($kind)
    {
        return self::getType($kind) == 'weapon';
    }
    
    public static function isObject($kind)
    {
        return self::getType($kind) == 'object';
    }
    
    public static function isChest($kind)
    {
        return $kind == TYPES_ENTITIES_CHEST;
    }
    
    public static function isItem($kind)
    {
        return (self::isWeapon($kind) || self::isArmor($kind) || (self::isObject($kind) && !self::isChest($kind)));
    }
    
    public static function isHealingItem($kind)
    {
        return $kind == TYPES_ENTITIES_FLASK || $kind == TYPES_ENTITIES_BURGER;
    }
    
    public static function isExpendableItem($kind)
    {
        return self::isHealingItem($kind) || $kind == TYPES_ENTITIES_FIREPOTION || $kind == TYPES_ENTITIES_CAKE;
    }
    
    public static function getKindFromString($kind)
    {
        return self::$stringToKindsMap[$kind][0];
    }
    
    public static function getKindAsString($kind)
    {
        $kinds_to_string_map = self::getKindsToStringMap();
        return $kinds_to_string_map[$kind];
    }
    
    public static function forEachKind($callback)
    {
        foreach(self::$stringToKindsMap as $key=>$kind)
        {
            call_user_func($callback, $kind[0], $key);
        }
    }
    
    public static function forEachArmor($callback)
    {
        self::forEachKind(function($kind, $kind_name) use ($callback)
        {
            if(self::isArmor($kind))
            {
                call_user_func($callback, $kind, $kind_name);
            }
        });
    }
    
    public static function forEachMobOrNpcKind($callback)
    {
        self::forEachKind(function($kind, $kind_name) use ($callback)
        {
            if(self::isMob($kind) || self::isNpc($kind))
            {
                call_user_func($callback, $kind, $kind_name);
            }
        });
    }
    
    public static function forEachArmorKind($callback)
    {
        self::forEachKind(function($kind, $kind_name) use ($callback)
        {
            if(self::isArmor($kind))
            {
                call_user_func($callback, $kind, $kind_name);
            }
        });
    }
    
    public static function getOrientationAsString($orientation)
    {
        switch ($orientation)
        {
            case TYPES_ORIENTATIONS_LEFT: 
                return 'left';
            case TYPES_ORIENTATIONS_RIGHT:
                return 'right';
            case TYPES_ORIENTATIONS_UP:
                return 'up';
            case TYPES_ORIENTATIONS_DOWN:
                return 'down';
        }
    }
    
    public static function getRandomItemKind()
    {
        if(!self::$randomItemKind)
        {
            $all = array_merge(array_keys(self::$rankedArmors) , array_keys(self::$rankedWeapons));
            $forbidden = array(TYPES_ENTITIES_SWORD1, TYPES_ENTITIES_CLOTHARMOR);
            $items_kinds = array_diff($all, $forbidden);
            self::$randomItemKind = $items_kinds[array_rand($items_kinds)];
        }
        return self::$randomItemKind[array_rand(self::$randomItemKind)];
    }
    
    public static function getMessageTypeAsString($type)
    {
        return isset(self::$typesToString['Messages'][$type]) ? self::$typesToString['Messages'][$type] : 'UNKNOWN';
    }
    
}
