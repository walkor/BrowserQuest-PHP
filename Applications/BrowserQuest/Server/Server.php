<?php 
namespace Server;
use \Workerman\Worker;

class Server extends Worker
{
    public function init($port) 
    {
        $this->port = $port;
    }
    
    public function onConnect($callback) 
    {
        $this->connectionCallback = $callback;
    }
    
    public function onError($callback) 
    {
        $this->errorCallback = $callback;
    }
    
    public function forEachConnection($callback) 
    {
        foreach($this->_connections as $con)
        {
            call_user_func($callback, $con);
        }
    }
    
    public function addConnection($connection) 
    {
        $this->_connections[$connection->id] = connection;
    }
    
    public function removeConnection($id) 
    {
        unset($this->_connections[$id]);
    }
    
    public function getConnection($id) 
    {
        return $this->_connections[id];
    }
    
    public function broadcast($data)
    {
        foreach($this->connections as $connection)
        {
            $connection->send($data);
        }
    }
}

class Connection
{
    public function __construct($id, $connection, $server) 
    {
        $this->_connection = $connection;
        $this->_server = $server;
        $this->id = $id;
    }
    
    public function onClose($callback) 
    {
        $this->closeCallback = $callback;
    }
    
    public function listen($callback) 
    {
        $this->listenCallback = $callback;
    }
    
    public function broadcast($message) 
    {
        throw new \Exception( "Not implemented");
    }
    
    public function send($message) 
    {
        throw new \Exception( "Not implemented");
    }
    
    public function sendUTF8($data) 
    {
        throw new \Exception( "Not implemented");
    }
    
    public function close($logError) 
    {
        echo "Closing connection to ". $this->_connection->getRemoteIp().". Error: ". $logError;
        $this->_connection->close();
    }
}