<?php
namespace JSONAPI\api;

class World
{
	private $plugin;

	function __construct($plugin)
	{
		$this->plugin = $plugin;
	}
	
	public function all() {
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
	
	public function get() {
		$server = $this->plugin->getServer();
		
		return array(
			'name' => $server->getDefaultLevel()
		);
	}
}