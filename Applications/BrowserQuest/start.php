<?php 
use \Workerman\WebServer;
use \Workerman\Worker;
use \Server\Utils;
use \Server\Player;
use \Server\WorldServer;

$server = new \Server\Server();
$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
$worlds = array();

foreach(range(0, $config['nb_worlds']) as $i)
{
    $world = new WorldServer('world'. ($i+1), $config['nb_players_per_world'], $server);
    $world->run($config['map_filepath']);
    $worlds[] = $world;
}

$ws_worker = new Worker('Websocket://0.0.0.0:8000');
$ws_worker->onConnect = function($connection) use ($server, $config)
{
    $connection->id = (int)$connection->getSocket();
    $connection->server = $server;
    //$server->addConnection($connection);
    if($server->connectionCallback)
    {
        call_user_func($server->connectionCallback);
    }
    $world = Utils::detect(worlds, function($world) 
    {
        return $world->playerCount < $config->nb_players_per_world;
    });
    if($world && $world->connectCallback)
    {
        call_user_func($world->connectCallback, new Player($connection, $world));
    }
};

$ws_worker->onMessage = function($connection, $data)
{
    
};

// WebServer
$web = new WebServer("http://0.0.0.0:8787");

$web->count = 2;

$web->addRoot('www.your_domain.com', __DIR__.'/Web');
