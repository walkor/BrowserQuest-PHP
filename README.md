# BrowserQuest-PHP
BrowserQuest server in PHP

![BrowserQuest width workerman](https://github.com/walkor/BrowserQuest-PHP/blob/master/Web/img/screenshot.jpg?raw=true)

## 安装 － Install
1、git clone https://github.com/walkor/BrowserQuest-PHP

2、composer install 

3、更改配置 Web/config/config_local.json 中的host为当前服务器ip地址或者域名

## Lniux 启动停止 - Start and Stop for Linux
以debug模式启动 ```php start.php start``` <br>
以daemon模式启动 ```php start.php start -d```  <br>
查看状态 ```php start.php status```   <br>
停止 ```php start.php stop```  <br>

## Windows 启动停止 - Start and Stop for Windows
双击 start_for_win.bat启动，按ctrl+c停止 <br>
Double click start_for_win.bat for start.
Press ctrl + c to stop.

## 说明 - Description
本游戏是由[BrowserQuest](https://github.com/mozilla/BrowserQuest)修改而来，主要是将后端nodejs部分用php（[workerman框架](https://github.com/walkor/workerman)）重写

## 在线演示 - Live Demo
[http://demos.workerman.net/browserquest/](http://demos.workerman.net/browserquest/)

## 原Repo - Original Repo
[https://github.com/mozilla/BrowserQuest](https://github.com/mozilla/BrowserQuest)
