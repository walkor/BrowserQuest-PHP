<?php 
use \Workerman\WebServer;
use \Workerman\Worker;
use \Server\Utils;
use \Server\Player;
use \Server\WorldServer;

// 自动加载类
require_once __DIR__ . '/../../Workerman/Autoloader.php';

// BrowserQuest Server
$ws_worker = new Worker('Websocket://0.0.0.0:8000');
$ws_worker->onWorkerStart = function($ws_worker)
{
    $ws_worker->server = new \Server\Server();
    $ws_worker->config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
    $ws_worker->worlds = array();
    
    foreach(range(0, $ws_worker->config['nb_worlds']-1) as $i)
    {
        $world = new WorldServer('world'. ($i+1), $ws_worker->config['nb_players_per_world'], $ws_worker);
        $world->run($ws_worker->config['map_filepath']);
        $ws_worker->worlds[] = $world;
    }
};

$ws_worker->onConnect = function($connection) use ($ws_worker)
{
    $connection->id = (int)$connection->getSocket();
    $connection->server = $ws_worker->server;
    if(isset($server->connectionCallback))
    {
        call_user_func($ws_worker->server->connectionCallback);
    }
    $world = Utils::detect($ws_worker->worlds, function($world)use($ws_worker) 
    {
        return $world->playerCount < $ws_worker->config['nb_players_per_world'];
    });
    $world->updatePopulation(null);
    if($world && isset($world->connectCallback))
    {
        call_user_func($world->connectCallback, new Player($connection, $world));
    }
};

// WebServer
$web = new WebServer("http://0.0.0.0:8787");

$web->count = 6;

$web->addRoot('www.your_domain.com', __DIR__.'/Web');

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
