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
use \Workerman\Lib\Timer;

class Player extends Character
{
    public $hasEnteredGame = false;
    public $isDead = false;
    public $haters = array();
    public $lastCheckpoint = array();
    public $disconnectTimeout = 0;
    public $armor = 0;
    public $armorLevel = 0;
    public $connection;
    public $server;
    public $weaponLevel = 0;
    
    public function __construct($connection, $worldServer)
    {
        $this->server = $worldServer;
        $this->connection = $connection;
        parent::__construct($this->connection->id, 'player', TYPES_ENTITIES_WARRIOR, 0, 0, '');
        $this->hasEnteredGame = false;
        $this->isDead = false;
        $this->haters = array();
        $this->lastCheckpoint = null;
        $this->formatChecker = new FormatChecker();
        $this->disconnectTimeout = 0;
        $this->connection->onMessage = array($this, 'onClientMessage');
        $this->connection->onClose = array($this, 'onClientclose');
        $this->connection->onWebSocketConnect = function($con)
        {
            $con->send('go');
        };
    }
    
    public function onClientMessage($connection, $data)
    {
        $message = json_decode($data, true);
        $action = $message[0];
        
        if(!$this->hasEnteredGame && $action !== TYPES_MESSAGES_HELLO)
        {
            $this->connection->close("Invalid handshake message: ". $data);
            return;
        }
        
        $this->resetTimeout();
        
        if($action === TYPES_MESSAGES_HELLO) 
        {
            $name = $message[1];
            $this->name = $name === "" ? "lorem ipsum" : $name;
            $this->kind = TYPES_ENTITIES_WARRIOR;
            $this->equipArmor($message[2]);
            $this->equipWeapon($message[3]);
            $this->orientation = Utils::randomOrientation();
            $this->updateHitPoints();
            $this->updatePosition();
            
            $this->server->addPlayer($this);
            call_user_func($this->server->enterCallback, $this);
            
            $this->connection->send(json_encode(array(TYPES_MESSAGES_WELCOME, $this->id, $this->name, $this->x, $this->y, $this->hitPoints)));
            $this->hasEnteredGame = true;
            $this->isDead = false;
        }
        elseif($action == TYPES_MESSAGES_WHO)
        {
            array_shift($message);
            $this->server->pushSpawnsToPlayer($this, $message);
        }
        else if($action === TYPES_MESSAGES_ZONE) {
            call_user_func($this->zoneCallback);
        }
        else if($action == TYPES_MESSAGES_CHAT) 
        {
            $msg = trim($message[1]);
            
            // Sanitized messages may become empty. No need to broadcast empty chat messages.
            if($msg) 
            {
                $this->broadcastToZone(new Messages\Chat($this, $msg), false);
            }
        }
        else if($action == TYPES_MESSAGES_MOVE) {
            if($this->moveCallback) 
            {
                $x = $message[1];
                $y = $message[2];
                
                if($this->server->isValidPosition($x, $y)) {
                    $this->setPosition($x, $y);
                    $this->clearTarget();
                    
                    $this->broadcast(new Messages\Move($this));
                    call_user_func($this->moveCallback, $this->x, $this->y);
                }
            }
        }
        else if($action == TYPES_MESSAGES_LOOTMOVE) {
            if($this->lootmoveCallback) 
            {
                $this->setPosition($message[1], $message[2]);
                
                $item = $this->server->getEntityById($message[3]);
                if($item) 
                {
                    $this->clearTarget();
                    $this->broadcast(new Messages\LootMove($this, $item));
                    call_user_func($this->lootmoveCallback, $this->x, $this->y);
                }
            }
        }
        else if($action == TYPES_MESSAGES_AGGRO) {
            if($this->moveCallback) 
            {
                $this->server->handleMobHate($message[1], $this->id, 5);
            }
        }
        else if($action == TYPES_MESSAGES_ATTACK) {
            $mob = $this->server->getEntityById($message[1]);
            if($mob) 
            {
                $this->setTarget($mob);
                $this->server->broadcastAttacker($this);
            }
        }
        else if($action == TYPES_MESSAGES_HIT) {
            $mob = $this->server->getEntityById($message[1]);
            if($mob) 
            {
                $dmg = Formulas::dmg($this->weaponLevel, $mob->armorLevel);
                
                if($dmg > 0 && is_callable(array($mob, 'receiveDamage')))
                {
                    $mob->receiveDamage($dmg, $this->id);
                    $this->server->handleMobHate($mob->id, $this->id, $dmg);
                    $this->server->handleHurtEntity($mob, $this, $dmg);
                }
            }
        }
        else if($action == TYPES_MESSAGES_HURT) {
            $mob = $this->server->getEntityById($message[1]);
            if($mob && $this->hitPoints > 0) 
            {
                $this->hitPoints -= Formulas::dmg($mob->weaponLevel, $this->armorLevel);
                $this->server->handleHurtEntity($this);
                
                if($this->hitPoints <= 0) 
                {
                    $this->isDead = true;
                    if(!empty($this->firepotionTimeout)) 
                    {
                        Timer::del($this->firepotionTimeout);
                        $this->firepotionTimeout = 0;
                    }
                }
            }
        }
        else if($action == TYPES_MESSAGES_LOOT) {
            $item = $this->server->getEntityById($message[1]);
            
            if($item) 
            {
                $kind = $item->kind;
                
                if(Types::isItem($kind)) 
                {
                    $this->broadcast($item->despawn());
                    $this->server->removeEntity($item);
                    
                    if($kind == TYPES_ENTITIES_FIREPOTION) 
                    {
                        $this->updateHitPoints();
                        $this->broadcast($this->equip(TYPES_ENTITIES_FIREFOX));
                        $this->firepotionTimeout = Timer::add(15, array($this, 'firepotionTimeoutCallback'), array(), false);
                        $hitpoints = new Messages\HitPoints($this->maxHitPoints);
                        $data = $hitpoints->serialize();
                        $this->connection->send(json_encode($data));
                    } 
                    else if(Types::isHealingItem($kind)) 
                    {
                        $amount = 0;
                        switch($kind) 
                        {
                            case TYPES_ENTITIES_FLASK: 
                                $amount = 40;
                                break;
                            case TYPES_ENTITIES_BURGER: 
                                $amount = 100;
                                break;
                        }
                        
                        if(!$this->hasFullHealth()) 
                        {
                            $this->regenHealthBy($amount);
                            $this->server->pushToPlayer($this, $this->health());
                        }
                    } 
                    else if(Types::isArmor($kind) || Types::isWeapon($kind)) 
                    {
                        $this->equipItem($item);
                        $this->broadcast($this->equip($kind));
                    }
                }
            }
        }
        else if($action == TYPES_MESSAGES_TELEPORT) {
            $x = $message[1];
            $y = $message[2];
            
            if($this->server->isValidPosition($x, $y)) 
            {
                $this->setPosition($x, $y);
                $this->clearTarget();
                
                $this->broadcast(new Messages\Teleport($this));
                
                $this->server->handlePlayerVanish($this);
                $this->server->pushRelevantEntityListTo($this);
            }
        }
        else if($action == TYPES_MESSAGES_OPEN) {
            $chest = $this->server->getEntityById($message[1]);
            if($chest && $chest instanceof Chest) 
            {
                $this->server->handleOpenedChest($chest, $this);
            }
        }
        else if($action == TYPES_MESSAGES_CHECK) {
            $checkpoint = $this->server->map->getCheckpoint($message[1]);
            if($checkpoint) 
            {
                $this->lastCheckpoint = $checkpoint;
            }
        }
        else 
        {
            if(isset($this->messageCallback)) 
            {
                call_user_func($this->messageCallback, $message);
            }
        }
    }
    
    public function onClientClose()
    {
        if(!empty($this->firepotionTimeout)) 
        {
            Timer::del($this->firepotionTimeout);
            $this->firepotionTimeout = 0;
        }
        Timer::del($this->disconnectTimeout);
        $this->disconnectTimeout = 0;
        if(isset($this->exitCallback)) 
        {
            call_user_func($this->exitCallback);
        }
    }
    
    public function firepotionTimeoutCallback()
    {
        $this->broadcast($this->equip($this->armor)); // return to normal after 15 sec
        $this->firepotionTimeout = 0;
    }
    
    public function destroy()
    {
        $this->forEachAttacker(function($mob) 
        {
            $mob->clearTarget();
        });
        $this->attackers = array();
        
        $this->forEachHater(array($this, 'forEachHaterCallback'));
        $this->haters = array();
    }
    
    public function forEachHaterCallback($mob)
    {
        $mob->forgetPlayer($this->id);
    }
    
    public function getState()
    {
        $basestate = $this->_getBaseState();
        $state = array($this->name, $this->orientation, $this->armor, $this->weapon);
        
        if($this->target) 
        {
            $state[] =$this->target;
        }
        return array_merge($basestate, $state);
    }
    
    public function send($message)
    {
        $this->connection->send($message);
    }
    
    public function broadcast($message, $ignoreSelf = true)
    {
        if($this->broadcastCallback) 
        {
            call_user_func($this->broadcastCallback, $message, $ignoreSelf);
        }
    }
    
    public function broadcastToZone($message, $ignoreSelf = true)
    {
        if($this->broadcastzoneCallback) 
        {
            call_user_func($this->broadcastzoneCallback, $message, $ignoreSelf);
        }
    }
    
    public function onExit($callback)
    {
         $this->exitCallback = $callback;
    }
    
    public function onMove($callback) 
    {
        $this->moveCallback = $callback;
    }
    
    public function onLootMove($callback)
    {
        $this->lootmoveCallback = $callback;
    }
    
    public function onZone($callback) 
    {
        $this->zoneCallback = $callback;
    }
    
    public function onOrient($callback) 
    {
        $this->orientCallback = $callback;
    }
    
    public function onMessage($callback) 
    {
        $this->messageCallback = $callback;
    }
    
    public function onBroadcast($callback) 
    {
        $this->broadcastCallback = $callback;
    }
    
    public function onBroadcastToZone($callback) 
    {
        $this->broadcastzoneCallback = $callback;
    }
    
    public function equip($item) 
    {
        return new Messages\EquipItem($this, $item);
    }
    
    public function addHater($mob) 
    {
        if($mob) {
            if(!(isset($this->haters[$mob->id]))) 
            {
                $this->haters[$mob->id] = $mob;
            }
        }
    }
    
    public function removeHater($mob) 
    {
        if($mob)
        {
            unset($this->haters[$mob->id]);
        }
    }
    
    public function forEachHater($callback) 
    {
        array_walk($this->haters, function($mob) use ($callback) 
        {
            call_user_func($callback, $mob);
        });
    }
    
    public function equipArmor($kind) 
    {
        $this->armor = $kind;
        $this->armorLevel = Properties::getArmorLevel($kind);
    }
    
    public function equipWeapon($kind) 
    {
        $this->weapon = $kind;
        $this->weaponLevel = Properties::getWeaponLevel($kind);
    }
    
    public function equipItem($item) 
    {
        if($item) {
            if(Types::isArmor($item->kind)) 
            {
                $this->equipArmor($item->kind);
                $this->updateHitPoints();
                $obj = new Messages\HitPoints($this->maxHitPoints);
                $data = $obj->serialize();
                $this->send(json_encode($data));
            } 
            else if(Types::isWeapon($item->kind)) 
            {
                $this->equipWeapon($item->kind);
            }
        }
    }
    
    public function updateHitPoints() 
    {
        $this->resetHitPoints(Formulas::hp($this->armorLevel));
    }
    
    public function updatePosition() 
    {
        if($this->requestposCallback) 
        {
            $pos = call_user_func($this->requestposCallback);
            $this->setPosition($pos['x'], $pos['y']);
        }
    }
    
    public function onRequestPosition($callback) 
    {
        $this->requestposCallback = $callback;
    }
    
    public function resetTimeout()
    {
        Timer::del($this->disconnectTimeout);
        // 15分钟
        $this->disconnectTimeout = Timer::add(15*60, array($this, 'timeout'), false);
    }
    
    public function timeout()
    {
        $this->connection->send('timeout');
        $this->connection->close('Player was idle for too long');
    }
}
