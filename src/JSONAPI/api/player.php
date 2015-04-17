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
		
		$onlinePlayers = $server->getOnlinePlayers();
		foreach ($onlinePlayers as $player) {
			array_push($result, array(
				'name' => $player->getName(),
				'nameTag' => $player->getNameTag(),
				'displayName' => $player->getDisplayName(),
				'ip' => $player->getAddress(),
				'port' => $player->getPort(),
				'gamemode' => $player->getGameMode(),
				'level' => $player->getLevel(),
				'health' => $player->getHealth(),
				'location' => array(
					'X' => $player->getX(),
					'Y' => $player->getY(),
					'Z' => $player->getZ(),
					'Level' => $player->getLevel()->getName()
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
			array_push($result, array(
				'name' => $player->getName(),
				'nameTag' => $player->getNameTag(),
				'displayName' => $player->getDisplayName(),
				'ip' => $player->getAddress(),
				'port' => $player->getPort(),
				'gamemode' => $player->getGameMode(),
				'level' => $player->getLevel(),
				'health' => $player->getHealth(),
				'location' => array(
					'X' => $player->getX(),
					'Y' => $player->getY(),
					'Z' => $player->getZ(),
					'Level' => $player->getLevel()->getName()
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
				'nameTag' => $player->getNameTag(),
				'displayName' => $player->getDisplayName(),
				'location' => array(
					'X' => $player->getX(),
					'Y' => $player->getY(),
					'Z' => $player->getZ(),
					'Level' => $player->getLevel()->getName()
				)
			));
		}
		return $result;
	}
}