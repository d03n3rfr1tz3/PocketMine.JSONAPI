<?php
namespace JSONAPI\api;

class Player
{
	private $plugin;

	function __construct($plugin)
	{
		$this->plugin = $plugin;
	}
	
	public function all() {
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
	
	public function online() {
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
	
	public function locations() {
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
	
	public function spawns() {
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