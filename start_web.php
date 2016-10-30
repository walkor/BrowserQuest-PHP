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
use \Workerman\WebServer;
use \Workerman\Worker;

// 这里使用workerman的WebServer运行Web目录。Web目录也可以用nginx/Apache等容器运行
$web = new WebServer("http://0.0.0.0:8787");

$web->count = 2;

$web->name = 'BrowserQuestWeb';

$web->addRoot('www.your_domain.com', __DIR__.'/Web');

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
