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

class Metrics
{
    public $config;
    public $client;
    public $isReady;
    public $readyCallback;
    public $totalPlayers = 0;
    
    public function __construct($config)
    {
        $this->config = config;
        $this->client = new \Memcached();
        $this->client->addServer($config['memcached_host'], $config['memcached_port']);
        $this->isReady = true;
        if($this->$readyCallback) 
        {
            call_user_func($this->readyCallback);
        }
    }
    
    public function ready($callback)
    {
        $this->readyCallback = $callback;
    }
    
    public function updatePlayerCounters($worlds, $updatedCallback)
    {
        $config = $this->config;
        $numServers = count(config.game_servers);
        $playerCount = 0;
        foreach($worlds as $world)
        {
            $playerCount += $world->playerCount;
        }
        
        if($this->isReady) 
        {
            // Set the number of players on this server
            $this->client->set('player_count_'.$config['server_name'], $playerCount);
            array_walk($config['game_servers'], array($this, 'update'), $updatedCallback);
        } 
    }
    
    protected function update($server, $key, $updatedCallback)
    {
        // Recalculate the total number of players and set it
            $result = $this->client->get('player_count_'.$server->name);
            $count = $result ? intval(result) : 0;
            $this->totalPlayers += $count;
            $numServers -= 1;
            if($numServers == 0) {
                 $this->client->set('total_players', $this->totalPlayers);
                 if($updatedCallback) 
                 {
                     call_user_func($this->totalPlayers);
                 }
            }
    }
    
    public function updateWorldDistribution($worlds)
    {
        $this->client->set('world_distribution_'.$this->config['server_name'], $worlds);
    }
    
    public function getOpenWorldCount($callback) 
    {
       $result = $this->client->get('world_count_'.$this->config['server_name']);
       call_user_func($callback, $result);
    }
    
    public function getTotalPlayers($callback) 
    {
       $result = $this->client->get('total_players');
        call_user_func($callback, $result);
    }
    
}