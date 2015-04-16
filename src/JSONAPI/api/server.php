<?php
namespace JSONAPI;

class Server
{
	private $plugin

	function __construct($plugin)
	{
		$this->plugin = $plugin;
	}
	
	public function config() {
		$server = $this->plugin->getServer();
		return array(
			'name' -> $server->getName(),
			'serverName' -> $server->getServerName(),
			'ip' -> $server->getIp(),
			'port' -> $server->getPort(),
			'version' -> $server->getVersion(),
			'maxPlayers' -> $server->getMaxPlayers(),
			'levelType' -> $server->getLevelType(),
			'gameMode' -> $server->getGameMode(),
			'difficulty' -> $server->getDifficulty(),
			'hardcore' -> $server->isHardcore(),
			'allowFlight' -> $server->getAllowFlight()
		);
	}
	
	public function state() {
		$server = $this->plugin->getServer();
		return array(
			
		);
	}
}