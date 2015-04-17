<?php
namespace JSONAPI\api;

use JSONAPI\api\IHandler;

class WorldHandler implements IHandler
{
	private $plugin;
	private $methods;

	function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->methods = array(
			'worlds' => 'getWorlds',
			'worlds.default' => 'getDefaultWorld',
			'worlds.name' => 'getWorldByName'
		);
	}
	
	public function handles() {
		return array_keys($this->methods);
	}
    public function handle($name, $arguments = null) {
		$this->{$this->methods[$name]}($arguments == null ? array() : $arguments);
	}
	
	private function getWorlds($arguments) {
		$server = $this->plugin->getServer();
		
		$result = array();
		
		$levels = $server->getLevels();
		foreach ($levels as $level) {
			array_push($result, array(
				'name' => $level->getName()
			));
		}
		return $result;
	}
	
	private function getDefaultWorld($arguments) {
		$server = $this->plugin->getServer();
		
		return array(
			'name' => $server->getDefaultLevel()
		);
	}
	
	private function getWorldByName($arguments) {
		$server = $this->plugin->getServer();
		
		return array(
			'name' => $server->getLevel($arguments[0])
		);
	}
}