<?php 
namespace Server;
use \Workerman\Worker;
require_once __DIR__ . 'Constants.php';

class WorldServer extends Worker
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
    
    
    
    public function __construct($socket_name)
    {
        $this->onConnect = array($this, 'onConnect');
        parent::__construct($socket_name);
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
        $this->connect_callback = $callback;
    }
    
    public function onPlayerEnter($callback) {
        $this->enter_callback = $callback;
    }
    
    public function onPlayerAdded($callback) {
        $this->added_callback = $callback;
    }
    
    public function onPlayerRemoved($callback) {
        $this->removed_callback = $callback;
    }
    
    public function onRegenTick($callback) {
        $this->regen_callback = $callback;
    }
    
    public function pushRelevantEntityListTo($player) {
        if($player && isset($this->groups[$player->group])) {
            $entities = array_keys($this->groups[$player->group].entities);
            $entities = Utils::reject($entities, function($id) { return $id == $player->id; });
            //$entities = array_map(function($id) { return intval($id); }, $entities);
            if(entities) 
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
            $this->pushToPlayer($player, new Messages\Spawn($entity));
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
    
    public function pushToGroup($groupId, $message, $ignoredPlayer) {
        $group = $this->groups[$groupId];
        if($group) 
        {
            foreach($goup->players as $playerId)
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
    
    public function pushToAdjacentGroups($groupId, $message, $ignoredPlayer) {
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
    
    public function pushBroadcast($message, $ignoredPlayer) 
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
                $connection = $this->server->getConnection($id);
                $connection->send(json_encode($this->outgoingQueues[$id]));
                $this->outgoingQueues[id] = array();
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
        echo "Removed " .Types::getKindAsString($entity->kind) ." : ". $entity->id;
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
        return npc;
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
        if($kind === TYPES_ENTITIES_CHEST) 
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
        // @todo bind
        $item->onRespawn($this->addStaticItem->bind($this, $item));
        
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
                if(player) 
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
                $this->chooseMobTarget($mob);
            }
        }
    }
    
    public function chooseMobTarget($mob, $hateRank) 
    {
        $player = $this->getEntityById($mob->getHatedPlayerId($hateRank));
        
        // If the mob is not already attacking the player, create an attack link between them.
        if($player && ! isset($player->attackers[$mob->id])) 
        {
            $this->clearMobAggroLink($mob);
            
            $player->addAttacker($mob);
            $mob->setTarget($player);
            
            $this->broadcastAttacker(mob);
            echo mob.id . " is now attacking " . $player->id;
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
            echo "Unknown entity : " . $id;
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
            $this->attackCallback($character);
        }
    }
    
    public function handleHurtEntity($entity, $attacker, $damage) 
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
                if(item) 
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
            $pos = $this->map->tileIndexToGridPosition($tid);
            
            if(Types::isNpc($kind)) 
            {
                $this->addNpc($kind, $pos['x'] + 1, $pos['y']);
            }
            if(Types::isMob($kind)) 
            {
                $mob = new Mob('7' . kind . ($count++), $kind, $pos['x'] + 1, $pos['y']);
                $self = $this;
                $mob->onRespawn(function() use ($mob, $self){
                    $mob->isDead = false;
                    $self->addMob($mob);
                    if($mob->area && $mob->area instanceof ChestArea)
                    {
                        $mob->area->addToArea($mob);
                    }
                });
                // @todo bind
                $mob->onMove($this->onMobMoveCallback->bind($this));
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
        $player->forEachAttacker(function($mob) use (&$previousAttackers, $self, $mob)
        {
            $previousAttackers->push($mob);
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
        
        foreach($itemName as $itemName => $percentage)
        {
            $p += $percentage;
            if($v <= $p) 
            {
                $item = $this->addItem($this->createItem(Types::getKindFromString($itemName), $mob->x, $mob->y));
                break;
            }
        }
        return $item;
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
            $pos = entity->getPositionNextTo($target);
            $valid = $this->isValidPosition($pos['x'], $pos['y']);
        }
        return pos;
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
        
        if($entity && $entity->group) 
        {
            $group = $this->groups[$entity->group];
            if($entity instanceof Player) 
            {
                $group->players = Utils::reject($group->players, function($id) use($entity) { return id === $entity->id; });
            }
            
            $this->map->forEachAdjacentGroup($entity->group, function($id) use ($entity, &$oldGroups) 
            {
                if(isset($this->groups[$id]->entities[$entity->id]))
                {
                    unset($this->groups[$id]->entities[$entity->id]);
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
            $this->map->forEachAdjacentGroup($groupId, function($id) use ($self, $isChest, $isItem, $isDroppedItem)
            {
                $group = $self->groups[id];
                if(group) 
                {
                    if(!in_array($entity->id, $group->entities)
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
            $this->map->forEachAdjacentGroup($groupId, function($id) use ($self, $newGroups, $entity, $groupId)
            {
                $self->groups[$id]->entities[$entity->id] = $entity;
                $newGroups[] = $id;
            });
            $entity->group = $groupId;
            
            if($entity instanceof Player) 
            {
                $slef->groups[$groupId]->players[] = $entity->id;
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
            if(!$entity->group || ($entity->group && $entity->group !== $groupId)) 
            {
                $hasChangedGroups = true;
                $this->addAsIncomingToGroup($entity, $groupId);
                $oldGroups = $this->removeFromGroups($entity);
                $newGroups = $this->addToGroup($entity, $groupId);
                
                if(count($oldGroups) > 0) 
                {
                    $entity->recentlyLeftGroups = array_diff($oldGroups, $newGroups);
                    echo "group diff: " . $entity->recentlyLeftGroups;
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
            $this->map->forEachGroup(function($id) 
            {
                $spawns = array();
                if($this->groups[$id]->incoming) 
                {
                    foreach($this->groups[$id]->incoming as $entity)
                    {
                        if($entity instanceof Player) 
                        {
                            $this->pushToGroup($id, new Messages\Spawn($entity), $entity->id);
                        } 
                        else 
                        {
                            $this->pushToGroup(id, new Messages\Spawn($entity));
                        }
                    }
                    foreach($this->groups[$id]->incoming as $entity)
                    {
                        if($entity instanceof Player) 
                        {
                            $this->pushToGroup($id, new Messages\Spawn($entity), $entity->id);
                        } 
                        else 
                        {
                            $this->pushToGroup(id, new Messages\Spawn($entity));
                        }
                    }
                    $this->groups[$id]->incoming = array();
                }
            });
        }
    }
    
    moveEntity: function(entity, x, y) {
        if(entity) {
            entity.setPosition(x, y);
            $this->handleEntityGroupMembership(entity);
        }
    }
    
    handleItemDespawn: function(item) {
        var self = this;
        
        if(item) {
            item.handleDespawn({
                beforeBlinkDelay: 10000,
                blinkCallback: function() {
                    $this->pushToAdjacentGroups(item.group, new Messages.Blink(item));
                }
                blinkingDuration: 4000,
                despawnCallback: function() {
                    $this->pushToAdjacentGroups(item.group, new Messages.Destroy(item));
                    $this->removeEntity(item);
                }
            });
        }
    }
    
    handleEmptyMobArea: function(area) {

    }
    
    handleEmptyChestArea: function(area) {
        if(area) {
            var chest = $this->addItem($this->createChest(area.chestX, area.chestY, area.items));
            $this->handleItemDespawn(chest);
        }
    }
    
    handleOpenedChest: function(chest, player) {
        $this->pushToAdjacentGroups(chest.group, chest.despawn());
        $this->removeEntity(chest);
        
        var kind = chest.getRandomItem();
        if(kind) {
            var item = $this->addItemFromChest(kind, chest.x, chest.y);
            $this->handleItemDespawn(item);
        }
    }
    
    tryAddingMobToChestArea: function(mob) {
        _.each($this->chestAreas, function(area) {
            if(area.contains(mob)) {
                area.addToArea(mob);
            }
        });
    }
    
    updatePopulation: function(totalPlayers) {
        $this->pushBroadcast(new Messages.Population($this->playerCount, totalPlayers ? totalPlayers : $this->playerCount));
    }
    public function onConnect($connection)
    {
        $connection->onWebSocketConnect = array($this, 'onWebSocketConnect');
    }
    
    public function onWebSocketConnect($connection)
    {
        
    }
}