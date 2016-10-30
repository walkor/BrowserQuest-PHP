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

class Server
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
        foreach($this->_connections as $connection)
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