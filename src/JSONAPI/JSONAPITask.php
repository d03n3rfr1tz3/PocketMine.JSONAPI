<?php
namespace JSONAPI;

use pocketmine\scheduler\PluginTask;

class JSONAPITask extends PluginTask
{
	private $i;
	private $plugin;
	
	public function __construct($plugin) {
		parent::__construct($plugin);
	}
	
	public function onRun($tick) {
		$this->getOwner()->getApiServer()->run_once($this->i++ == 0);
	}
}