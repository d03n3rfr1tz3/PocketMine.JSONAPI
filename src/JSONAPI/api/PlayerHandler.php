<?php
namespace JSONAPI\api;

use JSONAPI\api\IHandler;

class PlayerHandler implements IHandler
{
	private $plugin;
	private $methods;

	function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->methods = array(
			'players.name' => 'getPlayerByName',
			'players.online' => 'getOnlinePlayers',
			'players.online.location' => 'getLocations',
			'players.offline' => 'getOfflinePlayers',
			'players.offline.spawn' => 'getSpawns'
		);
	}
	
	public function handles() {
		return array_keys($this->methods);
	}
    public function handle($name, $arguments = null) {
		return $this->{$this->methods[$name]}($arguments == null ? array() : $arguments);
	}
	
	private function getPlayerByName($arguments) {
		$server = $this->plugin->getServer();
		
		$result = array();
		
		$player = $server->getOfflinePlayer($arguments[0]);
		
		$location = method_exists($player, 'getLocation') ? $player->getLocation() : null;
		$spawn = method_exists($player, 'getSpawn') ? $player->getSpawn() : null;
		return array(
			'name' => $player->getName(),
			'ip' => method_exists($player, 'getAddress') ? $player->getAddress() : null,
			'port' => method_exists($player, 'getPort') ? $player->getPort() : null,
			'gameMode' => method_exists($player, 'getGameMode') ? $player->getGameMode() : $server->getGameMode(),
			'health' => method_exists($player, 'getHealth') ? $player->getHealth() : null,
			'worldInfo' => array(
				'name' => method_exists($player, 'getLevel') ? $player->getLevel()->getName() : $server->getDefaultLevel()->getName()
			),
			'location' => $location == null ? null : array(
				'x' => $location->getX(),
				'y' => $location->getY(),
				'z' => $location->getZ()
			),
			'spawn' => $spawn == null ? null : array(
				'x' => $spawn->getX(),
				'y' => $spawn->getY(),
				'z' => $spawn->getZ()
			),
			'firstPlayed' => round($player->getFirstPlayed() / 1000),
			'lastPlayed' => round($player->getLastPlayed() / 1000)
		);
	}
	
	private function getOnlinePlayers($arguments) {
		$server = $this->plugin->getServer();
		
		$result = array();
		
		$onlinePlayers = $server->getOnlinePlayers();
		foreach ($onlinePlayers as $player) {
			$location = method_exists($player, 'getLocation') ? $player->getLocation() : null;
			$spawn = method_exists($player, 'getSpawn') ? $player->getSpawn() : null;
			array_push($result, array(
				'name' => $player->getName(),
				'ip' => $player->getAddress(),
				'port' => $player->getPort(),
				'gameMode' => $player->getGameMode(),
				'health' => $player->getHealth(),
				'worldInfo' => array(
					'name' => method_exists($player, 'getLevel') ? $player->getLevel()->getName() : $server->getDefaultLevel()->getName()
				),
				'location' => $location == null ? null : array(
					'x' => $location->getX(),
					'y' => $location->getY(),
					'z' => $location->getZ()
				),
				'spawn' => $spawn == null ? null : array(
					'x' => $spawn->getX(),
					'y' => $spawn->getY(),
					'z' => $spawn->getZ()
				),
				'firstPlayed' => round($player->getFirstPlayed() / 1000),
				'lastPlayed' => round($player->getLastPlayed() / 1000)
			));
		}
		return $result;
	}
	
	private function getOfflinePlayers($arguments) {
		$server = $this->plugin->getServer();
		
		$result = array();
		$onlineNames = array();
		
		$onlinePlayers = $server->getOnlinePlayers();
		foreach ($onlinePlayers as $player) {
			$name = $player->getName();
			$location = method_exists($player, 'getLocation') ? $player->getLocation() : null;
			$spawn = method_exists($player, 'getSpawn') ? $player->getSpawn() : null;
			array_push($result, array(
				'name' => $name,
				'ip' => $player->getAddress(),
				'port' => $player->getPort(),
				'gameMode' => $player->getGameMode(),
				'health' => $player->getHealth(),
				'worldInfo' => array(
					'name' => method_exists($player, 'getLevel') ? $player->getLevel()->getName() : $server->getDefaultLevel()->getName()
				),
				'location' => $location == null ? null : array(
					'x' => $location->getX(),
					'y' => $location->getY(),
					'z' => $location->getZ()
				),
				'spawn' => $spawn == null ? null : array(
					'x' => $spawn->getX(),
					'y' => $spawn->getY(),
					'z' => $spawn->getZ()
				),
				'firstPlayed' => round($player->getFirstPlayed() / 1000),
				'lastPlayed' => round($player->getLastPlayed() / 1000)
			));
			array_push($onlineNames, $name);
		}
		
		$offlinePlayers = array();
		$offlinePlayerFiles = scandir($server->getDataPath()."players/");
		foreach ($offlinePlayerFiles as $file) {
			$name = substr($file, 0, strlen($file) - 4);
			if ($name == false || array_search($name, $onlineNames) !== false) continue;
			
			array_push($offlinePlayers, $server->getOfflinePlayer($name));
		}
		
		foreach ($offlinePlayers as $player) {
			$spawn = method_exists($player, 'getSpawn') ? $player->getSpawn() : null;
			array_push($result, array(
				'name' => $player->getName(),
				'gameMode' => method_exists($player, 'getGameMode') ? $player->getGameMode() : $server->getGameMode(),
				'health' => method_exists($player, 'getHealth') ? $player->getHealth() : null,
				'worldInfo' => array(
					'name' => method_exists($player, 'getLevel') ? $player->getLevel()->getName() : $server->getDefaultLevel()->getName()
				),
				'spawn' => $spawn == null ? null : array(
					'x' => $spawn->getX(),
					'y' => $spawn->getY(),
					'z' => $spawn->getZ()
				),
				'firstPlayed' => round($player->getFirstPlayed() / 1000),
				'lastPlayed' => round($player->getLastPlayed() / 1000)
			));
		}
		
		return $result;
	}
	
	private function getLocations($arguments) {
		$server = $this->plugin->getServer();
		
		$result = array();
		
		$onlinePlayers = $server->getOnlinePlayers();
		foreach ($onlinePlayers as $player) {
			array_push($result, array(
				'name' => $player->getName(),
				'world' => method_exists($player, 'getLevel') ? $player->getLevel()->getName() : $server->getDefaultLevel()->getName(),
				'location' => array(
					'x' => $player->getX(),
					'y' => $player->getY(),
					'z' => $player->getZ()
				)
			));
		}
		return $result;
	}
	
	private function getSpawns($arguments) {
		$server = $this->plugin->getServer();
		
		$result = array();
		$onlineNames = array();
		
		$onlinePlayers = $server->getOnlinePlayers();
		foreach ($onlinePlayers as $player) {
			$name = $player->getName();
			$spawn = method_exists($player, 'getSpawn') ? $player->getSpawn() : null;
			array_push($result, array(
				'name' => $name,
				'world' => method_exists($player, 'getLevel') ? $player->getLevel()->getName() : $server->getDefaultLevel()->getName(),
				'spawn' => $spawn == null ? null : array(
					'x' => $spawn->getX(),
					'y' => $spawn->getY(),
					'z' => $spawn->getZ()
				)
			));
			array_push($onlineNames, $name);
		}
		
		$offlinePlayers = array();
		$offlinePlayerFiles = scandir($server->getDataPath()."players/");
		foreach ($offlinePlayerFiles as $file) {
			$name = substr($file, 0, strlen($file) - 4);
			if ($name == false || array_search($name, $onlineNames) !== false) continue;
			
			array_push($offlinePlayers, $server->getOfflinePlayer($name));
		}
		
		foreach ($offlinePlayers as $player) {
			$spawn = method_exists($player, 'getSpawn') ? $player->getSpawn() : null;
			array_push($result, array(
				'name' => $player->getName(),
				'world' => method_exists($player, 'getLevel') ? $player->getLevel()->getName() : $server->getDefaultLevel()->getName(),
				'spawn' => $spawn == null ? null : array(
					'x' => $spawn->getX(),
					'y' => $spawn->getY(),
					'z' => $spawn->getZ()
				)
			));
		}
		
		return $result;
	}
}