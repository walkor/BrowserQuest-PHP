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
use \Workerman\Worker;
use \Workerman\Lib\Timer;

require_once __DIR__ . '/Constants.php';

class WorldServer 
{
    public $id;
    public $maxPlayers;
    public $server;
    public $ups;
    public $map;
    
    public $entities = array();
    public $players = array();
    public $mobs = array();
    public $attackers = array();
    public $items = array();
    public $equipping = array();
    public $hurt = array();
    public $npcs = array();
    public $mobAreas = array();
    public $chestAreas = array();
    public $groups = array();
    public $outgoingQueues = array();
    
    public $itemCount;
    public $playerCount;
    public $zoneGroupsReady;
    
    
    public function __construct($id, $maxPlayers, $websocketServer)
    {
        $this->id = $id;
        $this->maxPlayers = $maxPlayers;
        $this->server = $websocketServer;
        $this->ups = 50;
        $this->map = null;
        $this->entities = array();
        $this->players = array();
        $this->mobs =array();
        $this->attackers = array();
        $this->items = array();
        $this->equipping = array();
        $this->hurt = array();
        $this->npcs = array();
        $this->mobAreas = array();
        $this->chestAreas = array();
        $this->groups = array();
        
        $this->outgoingQueues = array();
        
        $this->itemCount = 0;
        $this->playerCount = 0;
        
        $this->zoneGroupsReady = false;
        $self = $this;
        $this->onPlayerConnect(function ($player)use($self)
        {
            $player->onRequestPosition(function()use($self, $player) 
            {
                if($player->lastCheckpoint) 
                {
                    return $player->lastCheckpoint->getRandomPosition();
                } else {
                    return $self->map->getRandomStartingPosition();
                }
            });
        });
        
        $this->onPlayerEnter(
                function($player) use ($self)
                {
                    echo $player->name . " has joined ". $self->id."\n";
                
                    if(!$player->hasEnteredGame) 
                    {
                        $self->incrementPlayerCount();
                    }
                
                    // Number of players in this world
                    $self->pushToPlayer($player, new Messages\Population($self->playerCount, 1));
                    $self->pushRelevantEntityListTo($player);
                
                    $moveCallback = function($x, $y) use($player, $self)
                    {
                        echo $player->name . " is moving to (" . $x . ", " . $y . ")\n";
                
                        $player->forEachAttacker(function($mob) use($player, $self)
                        {
                            $target = $self->getEntityById($mob->target);
                            if($target) 
                            {
                                $pos = $self->findPositionNextTo($mob, $target);
                                if($mob->distanceToSpawningPoint($pos['x'], $pos['y']) > 50) 
                                {
                                    $mob->clearTarget();
                                    $mob->forgetEveryone();
                                    $player->removeAttacker($mob);
                                } 
                                else 
                                {
                                    $self->moveEntity($mob, $pos['x'], $pos['y']);
                                }
                            }
                        });
                    };
                
                    $player->onMove($moveCallback);
                    $player->onLootMove($moveCallback);
                
                    $player->onZone(function() use($self, $player)
                    {
                        $hasChangedGroups = $self->handleEntityGroupMembership($player);
                
                        if($hasChangedGroups) 
                        {
                            $self->pushToPreviousGroups($player, new Messages\Destroy($player));
                            $self->pushRelevantEntityListTo($player);
                        }
                    });
                
                    $player->onBroadcast(function($message, $ignoreSelf) use($self, $player)
                    {
                        $self->pushToAdjacentGroups($player->group, $message, $ignoreSelf ? $player->id : null);
                    });
                
                    $player->onBroadcastToZone(function($message, $ignoreSelf) use($self, $player)
                    {
                        $self->pushToGroup($player->group, $message, $ignoreSelf ? $player->id : null);
                    });
                
                    $player->onExit(function() use($self, $player)
                    {
                        echo $player->name . " has left the game.\n";
                        $self->removePlayer($player);
                        $self->decrementPlayerCount();
                
                        if(isset($self->removedCallback)) 
                        {
                            call_user_func($self->removedCallback);
                        }
                    });
                
                    if(isset($self->addedCallback)) 
                    {
                        call_user_func($self->addedCallback);
                    }
                }
            );
        
        $this->onEntityAttack(function($attacker) use($self)
        {
            $target = $self->getEntityById($attacker->target);
            if($target && $attacker->type == "mob") 
            {
                $pos = $self->findPositionNextTo($attacker, $target);
                $self->moveEntity($attacker, $pos['x'], $pos['y']);
            }
        });
        
        $this->onRegenTick(function() use ($self)
        {
            $self->forEachCharacter(function($character) use ($self)
            {
                if(!$character->hasFullHealth()) 
                {
                    $character->regenHealthBy(floor($character->maxHitPoints / 25));
                    if($character->type == 'player') 
                    {
                        $self->pushToPlayer($character, $character->regen());
                    }
                }
            });
        });
    }
   
    public function run($mapFilePath)
    {
        $self = $this;
        
        $this->map = new Map($mapFilePath);
        
        $this->map->ready(function() use ($self) 
        {
            $self->initZoneGroups();
        
            $self->map->generateCollisionGrid();
        
            // Populate all mob "roaming" areas
            foreach($self->map->mobAreas as $a)
            {
                $area = new MobArea($a->id, $a->nb, $a->type, $a->x, $a->y, $a->width, $a->height, $self);
                $area->spawnMobs();
                // @todo bind
                //$area->onEmpty($self->handleEmptyMobArea->bind($self, area));
                $area->onEmpty(function() use ($self, $area){
                    call_user_func(array($self, 'handleEmptyMobArea'), $area);
                });
                $self->mobAreas[] =  $area;
            }
            
            // Create all chest areas
            foreach($self->map->chestAreas as $a)
            {
                $area = new ChestArea($a->id, $a->x, $a->y, $a->w, $a->h, $a->tx, $a->ty, $a->i, $self);
                $self->chestAreas[] = $area;
                // @todo bind
                $area->onEmpty(function()use($self, $area){
                    call_user_func(array($self, 'handleEmptyChestArea'), $area);
                });
            }
        
            // Spawn static chests
            foreach($self->map->staticChests as $chest)
            {
                $c = $self->createChest($chest->x, $chest->y, $chest->i);
                $self->addStaticItem($c);
            }
        
            // Spawn static entities
            $self->spawnStaticEntities();
        
            // Set maximum number of entities contained in each chest area
            foreach($self->chestAreas as $area)
            {
                $area->setNumberOfEntities(count($area->entities));
            }
        });
        
        $this->map->initMap();
        
        $regenCount = $this->ups * 2;
        $updateCount = 0;
        Timer::add(1/$this->ups, function() use ($self, $regenCount, &$updateCount) 
        {
            $self->processGroups();
            $self->processQueues();
        
            if($updateCount < $regenCount) 
            {
                $updateCount += 1;
            } 
            else 
            {
                if($self->regenCallback) 
                {
                    call_user_func($self->regenCallback);
                }
                $updateCount = 0;
            }
        });
        
        echo $this->id." created capacity: ".$this->maxPlayers." players \n";
    }
    
    public function setUpdatesPerSecond($ups) 
    {
        $this->ups = $ups;
    }
    
    public function onInit($callback) 
    {
        $this->initCallback = $callback;
    }

    public function onPlayerConnect($callback) 
    {
        $this->connectCallback = $callback;
    }
    
    public function onPlayerEnter($callback) {
        $this->enterCallback = $callback;
    }
    
    public function onPlayerAdded($callback) {
        $this->addedCallback = $callback;
    }
    
    public function onPlayerRemoved($callback) {
        $this->removedCallback = $callback;
    }
    
    public function onRegenTick($callback) {
        $this->regenCallback = $callback;
    }
    
    public function pushRelevantEntityListTo($player) {
        if($player && isset($this->groups[$player->group])) {
            $entities = array_keys($this->groups[$player->group]->entities);
            $entities = Utils::reject($entities, function($id)use($player) { return $id == $player->id; });
            //$entities = array_map(function($id) { return intval($id); }, $entities);
            if($entities) 
            {
                $this->pushToPlayer($player, new Messages\Lists($entities));
            }
        }
    }
    
    public function pushSpawnsToPlayer($player, $ids) 
    {
        foreach($ids as $id)
        {
            $entity = $this->getEntityById($id);
            if($entity)
            {
                $this->pushToPlayer($player, new Messages\Spawn($entity));
            }
            else
            {
                echo new \Exception("bad id:$id ids:" . json_encode($ids));
            }
        }
    }
    
    public function pushToPlayer($player, $message) 
    {
        if($player && isset($this->outgoingQueues[$player->id])) 
        {
            $this->outgoingQueues[$player->id][] = $message->serialize();
        }
        else 
        {
            echo "pushToPlayer: player was undefined";
        }
    }
    
    public function pushToGroup($groupId, $message, $ignoredPlayer=null) {
        $group = $this->groups[$groupId];
        if($group) 
        {
            foreach($group->players as $playerId)
            {
                if($playerId != $ignoredPlayer) 
                {
                    $this->pushToPlayer($this->getEntityById($playerId), $message);
                }
            }
        } 
        else 
        {
            echo "groupId: ".$groupId." is not a valid group";
        }
    }
    
    public function pushToAdjacentGroups($groupId, $message, $ignoredPlayer=0) {
        $self = $this;
        $this->map->forEachAdjacentGroup($groupId, function($id) use ($self, $message, $ignoredPlayer) 
        {
            $self->pushToGroup($id, $message, $ignoredPlayer);
        });
    }
    
    public function pushToPreviousGroups($player, $message) 
    {
        // Push this message to all groups which are not going to be updated anymore,
        // since the player left them.
        foreach($player->recentlyLeftGroups as $id)
        {
            $this->pushToGroup($id, $message);
        }
        $player->recentlyLeftGroups = array();
    }
    
    public function pushBroadcast($message, $ignoredPlayer = null) 
    {
        foreach($this->outgoingQueues as $id=>$item)
        {
            if($id != $ignoredPlayer)
            {
                $this->outgoingQueues[$id][] = $message->serialize();
            }
        }
    }
    
    public function processQueues() 
    {
        foreach($this->outgoingQueues as $id=>$item)
        {
            if($this->outgoingQueues[$id]) {
                $connection = $this->server->connections[$id];
                $connection->send(json_encode($this->outgoingQueues[$id]));
                $this->outgoingQueues[$id] = array();
            }
        }
    }
    
    public function addEntity($entity) 
    {
        $this->entities[$entity->id] = $entity;
        $this->handleEntityGroupMembership($entity);
    }
    
    public function removeEntity($entity) 
    {
        unset($this->entities[$entity->id], 
                $this->mobs[$entity->id], 
                $this->items[$entity->id]
                );
        
        if($entity->type === "mob") {
            $this->clearMobAggroLink($entity);
            $this->clearMobHateLinks($entity);
        }
        
        $entity->destroy();
        $this->removeFromGroups($entity);
        echo "Removed " .Types::getKindAsString($entity->kind) ." : ". $entity->id."\n";
    }
    
    public function addPlayer($player) 
    {
        $this->addEntity($player);
        $this->players[$player->id] = $player;
        $this->outgoingQueues[$player->id] = array();
    }
    
    public function removePlayer($player) 
    {
        $player->broadcast($player->despawn());
        $this->removeEntity($player);
        unset($this->players[$player->id], $this->outgoingQueues[$player->id]);
    }
    
    public function addMob($mob) 
    {
        $this->addEntity($mob);
        $this->mobs[$mob->id] = $mob;
    }
    
    public function addNpc($kind, $x, $y) 
    {
        $npc = new Npc('8'.$x.''.$y, $kind, $x, $y);
        $this->addEntity($npc);
        $this->npcs[$npc->id] = $npc;
        return $npc;
    }
    
    public function addItem($item) 
    {
        $this->addEntity($item);
        $this->items[$item->id] = $item;
        
        return $item;
    }

    public function createItem($kind, $x, $y) 
    {
        $id = '9'.($this->itemCount++);
        if($kind == TYPES_ENTITIES_CHEST) 
        {
            $item = new Chest($id, $x, $y);
        } 
        else 
        {
            $item = new Item($id, $kind, $x, $y);
        }
        return $item;
    }

    public function createChest($x, $y, $items) 
    {
        $chest = $this->createItem(TYPES_ENTITIES_CHEST, $x, $y);
        $chest->setItems($items);
        return $chest;
    }
    
    public function addStaticItem($item) 
    {
        $item->isStatic = true;
        $self = $this;
        // @todo bind
        //$item->onRespawn($this->addStaticItem->bind($this, $item));
        $item->onRespawn(function()use($self, $item){
            call_user_func(array($self, 'addStaticItem'), $item);
        });
        
        return $this->addItem($item);
    }
    
    public function addItemFromChest($kind, $x, $y) 
    {
        $item = $this->createItem($kind, $x, $y);
        $item->isFromChest = true;
        
        return $this->addItem($item);
    }
    
    /**
     * The mob will no longer be registered as an attacker of its current target.
     */
    public function clearMobAggroLink($mob) 
    {
        if($mob->target) 
        {
            $player = $this->getEntityById($mob->target);
            if($player) 
            {
                $player->removeAttacker($mob);
            }
        }
    }

    public function clearMobHateLinks($mob) 
    {
        if($mob) 
        {
            foreach($mob->hatelist as $obj)
            {
                $player = $this->getEntityById($obj->id);
                if($player) 
                {
                    $player->removeHater($mob);
                }
            }
        }
    }
    
    public function forEachEntity($callback) 
    {
        foreach($this->entities as $item)
        {
            call_user_func($callback, $item);
        }
    }
    
    public function forEachPlayer($callback) 
    {
        foreach($this->players as $player)
        {
            call_user_func($callback, $player);
        }
    }
    
    public function forEachMob($callback) 
    {
        foreach($this->mobs as $mob)
        {
            call_user_func($callback, $mob);
        }
    }
    
    public function forEachCharacter($callback) 
    {
        $this->forEachPlayer($callback);
        $this->forEachMob($callback);
    }
    
    public function handleMobHate($mobId, $playerId, $hatePoints) 
    {
        $mob = $this->getEntityById($mobId);
        $player = $this->getEntityById($playerId);
        if($player && $mob) {
            $mob->increaseHateFor($playerId, $hatePoints);
            $player->addHater($mob);
            
            if($mob->hitPoints > 0) 
            { // only choose a target if still alive
                $this->chooseMobTarget($mob, 0);
            }
        }
    }
    
    public function chooseMobTarget($mob, $hateRank = 0) 
    {
        $player = $this->getEntityById($mob->getHatedPlayerId($hateRank));
        
        // If the mob is not already attacking the player, create an attack link between them.
        if($player && ! isset($player->attackers[$mob->id])) 
        {
            $this->clearMobAggroLink($mob);
            
            $player->addAttacker($mob);
            $mob->setTarget($player);
            
            $this->broadcastAttacker($mob);
            echo $mob->id . " is now attacking " . $player->id."\n";
        }
    }
    
    public function onEntityAttack($callback) 
    {
        $this->attackCallback = $callback;
    }
    
    public function getEntityById($id) 
    {
        if(isset($this->entities[$id])) 
        {
            return $this->entities[$id];
        } 
        else 
        {
            echo "Unknown entity : $id\n";
        }
    }
    
    public function getPlayerCount() 
    {
        $count = 0;
        foreach($this->players as $p => $player)
        {
            if($this->players->hasOwnProperty($p))
            {
                $count += 1;
            }
        }
        return $count;
    }
    
    public function broadcastAttacker($character) 
    {
        if($character) 
        {
            $this->pushToAdjacentGroups($character->group, $character->attack(), $character->id);
        }
        if($this->attackCallback) 
        {
            call_user_func($this->attackCallback, $character);
        }
    }
    
    public function handleHurtEntity($entity, $attacker = null, $damage = 0) 
    {
        if($entity->type === 'player') 
        {
            // A player is only aware of his own hitpoints
            $this->pushToPlayer($entity, $entity->health());
        }
        
        if($entity->type === 'mob') 
        {
            // Let the mob's attacker (player) know how much damage was inflicted
            $this->pushToPlayer($attacker, new Messages\Damage($entity, $damage));
        }

        // If the entity is about to die
        if($entity->hitPoints <= 0) 
        {
            if($entity->type === "mob") 
            {
                $mob = $entity;
                $item = $this->getDroppedItem($mob);

                $this->pushToPlayer($attacker, new Messages\Kill($mob));
                $this->pushToAdjacentGroups($mob->group, $mob->despawn()); // Despawn must be enqueued before the item drop
                if($item) 
                {
                    $this->pushToAdjacentGroups($mob->group, $mob->drop($item));
                    $this->handleItemDespawn($item);
                }
            }
    
            if($entity->type === "player") 
            {
                $this->handlePlayerVanish($entity);
                $this->pushToAdjacentGroups($entity->group, $entity->despawn());
            }
    
            $this->removeEntity($entity);
        }
    }
    
    public function despawn($entity) 
    {
        $this->pushToAdjacentGroups($entity->group, $entity->despawn());

        if(isset($this->entities[$entity->id])) 
        {
            $this->removeEntity($entity);
        }
    }
    
    public function spawnStaticEntities() 
    {
        $count = 0;
        foreach($this->map->staticEntities as $tid=>$kindName)
        {
            $kind = Types::getKindFromString($kindName);
            $pos = $this->map->titleIndexToGridPosition($tid);
            
            if(Types::isNpc($kind)) 
            {
                $this->addNpc($kind, $pos['x'] + 1, $pos['y']);
            }
            if(Types::isMob($kind)) 
            {
                $mob = new Mob('7' . $kind . ($count++), $kind, $pos['x'] + 1, $pos['y']);
                $self = $this;
                $mob->onRespawn(function() use ($mob, $self){
                    $mob->isDead = false;
                    $self->addMob($mob);
                    if(!empty($mob->area) && $mob->area instanceof ChestArea)
                    {
                        $mob->area->addToArea($mob);
                    }
                });
                // @todo bind
                $mob->onMove(array($self, 'onMobMoveCallback'));
                $this->addMob($mob);
                $this->tryAddingMobToChestArea($mob);
            }
            if(Types::isItem($kind)) 
            {
                $this->addStaticItem($this->createItem($kind, $pos['x'] + 1, $pos['y']));
            }
        }
    }

    public function isValidPosition($x, $y) 
    {
        if($this->map && is_numeric($x) && is_numeric($y) && !$this->map->isOutOfBounds($x, $y) && !$this->map->isColliding($x, $y)) 
        {
            return true;
        }
        return false;
    }
    
    public function handlePlayerVanish($player) 
    {
       $previousAttackers = array();
        $self = $this;
        // When a player dies or teleports, all of his attackers go and attack their second most hated $player->
        $player->forEachAttacker(function($mob) use (&$previousAttackers, $self)
        {
            $previousAttackers[] =$mob;
            $self->chooseMobTarget($mob, 2);
        });
        
        
        foreach($previousAttackers as $mob)
        {
            $player->removeAttacker($mob);
            $mob->clearTarget();
            $mob->forgetPlayer($player->id, 1000);
        }
        
        $this->handleEntityGroupMembership($player);
    }
    
    public function setPlayerCount($count) 
    {
        $this->playerCount = $count;
    }
    
    public function incrementPlayerCount() 
    {
        $this->setPlayerCount($this->playerCount + 1);
    }
    
    public function decrementPlayerCount() 
    {
        if($this->playerCount > 0) 
        {
            $this->setPlayerCount($this->playerCount - 1);
        }
    }
    
    public function getDroppedItem($mob) 
    {
        $kind = Types::getKindAsString($mob->kind);
        $drops = Properties::$properties[$kind]['drops'];
        $v = rand(0, 100);
        $p = 0;
        
        foreach($drops as $itemName => $percentage)
        {
            $p += $percentage;
            if($v <= $p) 
            {
                $item = $this->addItem($this->createItem(Types::getKindFromString($itemName), $mob->x, $mob->y));
                return $item;
            }
        }
    }
    
    public function onMobMoveCallback($mob) 
    {
        $this->pushToAdjacentGroups($mob->group, new Messages\Move($mob));
        $this->handleEntityGroupMembership($mob);
    }
    
    public function findPositionNextTo($entity, $target) 
    {
        $valid = false;
        
        while(!$valid) 
        {
            $pos = $entity->getPositionNextTo($target);
            $valid = $this->isValidPosition($pos['x'], $pos['y']);
        }
        return $pos;
    }
    
    public function initZoneGroups() 
    {
        $self = $this;
        $this->map->forEachGroup(function($id) use ($self) 
        {
            $self->groups[$id] = (object)array('entities'=> array(),
                'players' => array(),
                'incoming'=> array()
             );
        });
        $this->zoneGroupsReady = true;
    }
    
    public function removeFromGroups($entity) 
    {
        $self = $this;
        $oldGroups = array();
        
        if($entity && isset($entity->group)) 
        {
            $group = $this->groups[$entity->group];
            if($entity instanceof Player) 
            {
                $group->players = Utils::reject($group->players, function($id) use($entity) { return $id == $entity->id; });
            }
            
            $this->map->forEachAdjacentGroup($entity->group, function($id) use ($entity, &$oldGroups, $self) 
            {
                if(isset($self->groups[$id]->entities[$entity->id]))
                {
                    unset($self->groups[$id]->entities[$entity->id]);
                    $oldGroups[] = $id;
                }
            });
            $entity->group = null;
        }
        return $oldGroups;
    }
    
    /**
     * Registers an entity as "incoming" into several groups, meaning that it just entered them.
     * All players inside these groups will receive a Spawn message when WorldServer.processGroups is called.
     */
    public function addAsIncomingToGroup($entity, $groupId) 
    {
        $self = $this;
        $isChest = $entity && $entity instanceof Chest;
        $isItem = $entity && $entity instanceof Item;
        $isDroppedItem =  $entity && $isItem && !$entity->isStatic && !$entity->isFromChest;
        
        if($entity && $groupId) 
        {
            $this->map->forEachAdjacentGroup($groupId, function($id) use ($self, $isChest, $isItem, $isDroppedItem, $entity)
            {
                $group = $self->groups[$id];
                if($group) 
                {
                    if(!isset($group->entities[$entity->id])
                    //  Items dropped off of mobs are handled differently via DROP messages. See handleHurtEntity.
                    && (!$isItem || $isChest || ($isItem && !$isDroppedItem))) 
                    {
                        $group->incoming[] = $entity;
                    }
                }
            });
        }
    }
    
    public function addToGroup($entity, $groupId) 
    {
        $self = $this;
        $newGroups = array();
        
        if($entity && $groupId && (isset($this->groups[$groupId]))) 
        {
            $this->map->forEachAdjacentGroup($groupId, function($id) use ($self, &$newGroups, $entity, $groupId)
            {
                $self->groups[$id]->entities[$entity->id] = $entity;
                $newGroups[] = $id;
            });
            $entity->group = $groupId;
            
            if($entity instanceof Player) 
            {
                $self->groups[$groupId]->players[] = $entity->id;
            }
        }
        return $newGroups;
    }
    
    public function logGroupPlayers($groupId) 
    {
        echo "Players inside group ".$groupId.":";
    }
    
    public function handleEntityGroupMembership($entity) 
    {
        $hasChangedGroups = false;
        if($entity) 
        {
            $groupId = $this->map->getGroupIdFromPosition($entity->x, $entity->y);
            if(empty($entity->group) || ($entity->group && $entity->group != $groupId)) 
            {
                $hasChangedGroups = true;
                $this->addAsIncomingToGroup($entity, $groupId);
                $oldGroups = $this->removeFromGroups($entity);
                $newGroups = $this->addToGroup($entity, $groupId);
                
                if(count($oldGroups) > 0) 
                {
                    $entity->recentlyLeftGroups = array_diff($oldGroups, $newGroups);
                    //echo "group diff: " . json_encode($entity->recentlyLeftGroups);
                }
            }
        }
        return $hasChangedGroups;
    }
    
    public function processGroups() 
    {
        $self = $this;
        
        if($this->zoneGroupsReady) 
        {
            $this->map->forEachGroup(function($id) use($self)
            {
                $spawns = array();
                if($self->groups[$id]->incoming) 
                {
                    foreach($self->groups[$id]->incoming as $entity)
                    {
                        if($entity instanceof Player) 
                        {
                            $self->pushToGroup($id, new Messages\Spawn($entity), $entity->id);
                        } 
                        else 
                        {
                            $self->pushToGroup($id, new Messages\Spawn($entity));
                        }
                    }
                    foreach($self->groups[$id]->incoming as $entity)
                    {
                        if($entity instanceof Player) 
                        {
                            $self->pushToGroup($id, new Messages\Spawn($entity), $entity->id);
                        } 
                        else 
                        {
                            $self->pushToGroup($id, new Messages\Spawn($entity));
                        }
                    }
                    $self->groups[$id]->incoming = array();
                }
            });
        }
    }
    
    public function moveEntity($entity, $x, $y) 
    {
        if($entity) 
        {
            $entity->setPosition($x, $y);
            $this->handleEntityGroupMembership($entity);
        }
    }
    
    public function handleItemDespawn($item) 
    {
        $self = $this;
        
        if($item) 
        {
            $item->handleDespawn(array(
                'beforeBlinkDelay'=>10000,
                'blinkCallback'=> function()use($self, $item){
                    $self->pushToAdjacentGroups($item->group, new Messages\Blink($item));
                },
                'blinkingDuration'=> 4000,
                'despawnCallback'=> function()use($self, $item) {
                    $self->pushToAdjacentGroups($item->group, new Messages\Destroy($item));
                    $self->removeEntity($item);
                }
            ));
        }
        
    }
    
    public function handleEmptyMobArea($area) 
    {

    }
    
    public function handleEmptyChestArea($area) 
    {
        if($area) 
        {
            $chest = $this->addItem($this->createChest($area->chestX, $area->chestY, $area->items));
            $this->handleItemDespawn($chest);
        }
    }
    
    public function handleOpenedChest($chest, $player) 
    {
        $this->pushToAdjacentGroups($chest->group, $chest->despawn());
        $this->removeEntity($chest);
        
        $kind = $chest->getRandomItem();
        if($kind) 
        {
            $item = $this->addItemFromChest($kind, $chest->x, $chest->y);
            $this->handleItemDespawn($item);
        }
    }
    
    public function tryAddingMobToChestArea($mob) 
    {
        foreach($this->chestAreas as $area)
        {
            if($area->contains($mob))
            {
                $area->addToArea($mob);
            }
        }
    }
    
    public function updatePopulation($totalPlayers) 
    {
        $this->pushBroadcast(new Messages\Population($this->playerCount, $totalPlayers ? $totalPlayers : $this->playerCount));
    }
    
    public function onConnect($connection)
    {
        $connection->onWebSocketConnect = array($this, 'onWebSocketConnect');
    }
    
    public function onWebSocketConnect($connection)
    {
        
    }
}
