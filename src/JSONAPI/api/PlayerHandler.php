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
		$this->{$this->methods[$name]}($arguments == null ? array() : $arguments);
	}
	
	private function getPlayerByName($arguments) {
		$server = $this->plugin->getServer();
		
		$result = array();
		
		$player = $server->getOfflinePlayer($arguments[0]);
		$spawn = method_exists($player, 'getSpawn') ? $player->getSpawn() : null;
			return array(
				'name' => $player->getName(),
				'ip' => method_exists($player, 'getAddress') ? $player->getAddress() : null,
				'port' => method_exists($player, 'getPort') ? $player->getPort() : null,
				'gamemode' => method_exists($player, 'getGameMode') ? $player->getGameMode() : $server->getGameMode(),
				'health' => method_exists($player, 'getHealth') ? $player->getHealth() : null,
				'location' => array(
					'X' => $player->getX(),
					'Y' => $player->getY(),
					'Z' => $player->getZ(),
					'world' => $player->getLevel()->getName()
				),
				'spawn' => $spawn == null ? null : array(
					'X' => $spawn->getX(),
					'Y' => $spawn->getY(),
					'Z' => $spawn->getZ(),
					'world' => $spawn->getLevel()->getName()
				),
				'firstplayed' => $player->getFirstPlayed(),
				'lastplayed' => $player->getLastPlayed()
			);
	}
	
	private function getOnlinePlayers($arguments) {
		$server = $this->plugin->getServer();
		
		$result = array();
		
		$onlinePlayers = $server->getOnlinePlayers();
		foreach ($onlinePlayers as $player) {
			$spawn = method_exists($player, 'getSpawn') ? $player->getSpawn() : null;
			array_push($result, array(
				'name' => $player->getName(),
				'ip' => $player->getAddress(),
				'port' => $player->getPort(),
				'gamemode' => $player->getGameMode(),
				'health' => $player->getHealth(),
				'location' => array(
					'X' => $player->getX(),
					'Y' => $player->getY(),
					'Z' => $player->getZ(),
					'world' => $player->getLevel()->getName()
				),
				'spawn' => $spawn == null ? null : array(
					'X' => $spawn->getX(),
					'Y' => $spawn->getY(),
					'Z' => $spawn->getZ(),
					'world' => $spawn->getLevel()->getName()
				),
				'firstplayed' => $player->getFirstPlayed(),
				'lastplayed' => $player->getLastPlayed()
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
			$spawn = method_exists($player, 'getSpawn') ? $player->getSpawn() : null;
			array_push($result, array(
				'name' => $name,
				'ip' => $player->getAddress(),
				'port' => $player->getPort(),
				'gamemode' => $player->getGameMode(),
				'health' => $player->getHealth(),
				'location' => array(
					'X' => $player->getX(),
					'Y' => $player->getY(),
					'Z' => $player->getZ(),
					'world' => $player->getLevel()->getName()
				),
				'spawn' => $spawn == null ? null : array(
					'X' => $spawn->getX(),
					'Y' => $spawn->getY(),
					'Z' => $spawn->getZ(),
					'world' => $spawn->getLevel()->getName()
				),
				'firstplayed' => $player->getFirstPlayed(),
				'lastplayed' => $player->getLastPlayed()
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
				'gamemode' => method_exists($player, 'getGameMode') ? $player->getGameMode() : $server->getGameMode(),
				'health' => method_exists($player, 'getHealth') ? $player->getHealth() : null,
				'spawn' => $spawn == null ? null : array(
					'X' => $spawn->getX(),
					'Y' => $spawn->getY(),
					'Z' => $spawn->getZ(),
					'world' => $spawn->getLevel()->getName()
				),
				'firstplayed' => $player->getFirstPlayed(),
				'lastplayed' => $player->getLastPlayed()
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
				'location' => array(
					'X' => $player->getX(),
					'Y' => $player->getY(),
					'Z' => $player->getZ(),
					'world' => $player->getLevel()->getName()
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
				'spawn' => $spawn == null ? null : array(
					'X' => $spawn->getX(),
					'Y' => $spawn->getY(),
					'Z' => $spawn->getZ(),
					'world' => $spawn->getLevel()->getName()
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
				'spawn' => $spawn == null ? null : array(
					'X' => $spawn->getX(),
					'Y' => $spawn->getY(),
					'Z' => $spawn->getZ(),
					'world' => $spawn->getLevel()->getName()
				)
			));
		}
		
		return $result;
	}
}