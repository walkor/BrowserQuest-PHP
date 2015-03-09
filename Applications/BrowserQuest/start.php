<?php 
use \Workerman\WebServer;
use \Workerman\Worker;

$ws_worker = new Worker('Websocket://0.0.0.0:8000');
$ws_worker->onMessage = function($con, $data)
{
    var_export($data);
};

// WebServer
$web = new WebServer("http://0.0.0.0:8787");

$web->count = 2;

$web->addRoot('www.your_domain.com', __DIR__.'/Web');
