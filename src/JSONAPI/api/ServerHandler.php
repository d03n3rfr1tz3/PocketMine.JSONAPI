<?php
namespace JSONAPI\api;

use JSONAPI\api\IHandler;

class ServerHandler implements IHandler
{
	private $plugin;
	private $methods;

	function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->methods = array(
			'server.setting' => 'getSettings',
			'server.state' => 'getState'
		);
	}
	
	public function handles() {
		return array_keys($this->methods);
	}
    public function handle($name, $arguments = null) {
		return $this->{$this->methods[$name]}($arguments == null ? array() : $arguments);
	}
	
	private function getSettings($arguments) {
		$server = $this->plugin->getServer();
		return array(
			'name' => $server->getServerName(),
			'motd' => $server->getMotd(),
			'ip' => $server->getIp(),
			'port' => $server->getPort(),
			'version' => $server->getVersion(),
			'maxPlayers' => $server->getMaxPlayers(),
			'levelType' => $server->getLevelType(),
			'gameMode' => $server->getGameMode(),
			'difficulty' => $server->getDifficulty(),
			'hardcore' => $server->isHardcore(),
			'allowFlight' => $server->getAllowFlight()
		);
	}
	
	private function getState($arguments) {
		$server = $this->plugin->getServer();
		return array(
			'maxPlayers' => $server->getMaxPlayers(),
			'players' => count($server->getOnlinePlayers()),
			'maxRam' => (int) preg_replace('/[^0-9]/', '', $server->getConfigString("memory-limit")),
			'ram' => round(memory_get_usage() / 1024 / 1024, 2),
			'cpuusage' => $server->getTickUsage()
		);
	}
}