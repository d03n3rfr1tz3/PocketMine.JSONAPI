<?php
namespace JSONAPI;

use pocketmine\plugin\PluginBase;

use JSONAPI\JSONAPIServer;
use JSONAPI\JSONAPITask;
use JSONAPI\api\Chat;
use JSONAPI\api\Player;
use JSONAPI\api\Server;
use JSONAPI\api\World;

class JSONAPI extends PluginBase
{
	private $handlers;
	private $server;
	private $task;
	
	function __construct()
	{
		$this->handlers = array(
			'chat' => new Chat($this),
			'player' => new Player($this),
			'server' => new Server($this),
			'world' => new World($this)
		);
	}
	
	public function onLoad()
	{
		$this->server = new JSONAPIServer($this);
		$this->task = new JSONAPITask($this);
		$this->getLogger()->info('JSONAPI is initialized!');
	}

	public function onEnable()
	{
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask($this->task, 20, 2);
		$this->getLogger()->info('JSONAPI is enabled!');
	}

	public function onDisable()
	{
		$this->getLogger()->info('JSONAPI is disabled!');
	}
	
	public function getApiServer() {
		return $this->server;
	}
	
	public function canHandleRequest($request)
	{
		return preg_match('#^/api/#i', $request->uri);
	}
	
	public function handleRequest($request)
	{
		$uri;
		preg_match('#^/api/([a-z]+)/([a-z]+)#i', $request->uri, $uri);
		$class = $uri[1];
		$method = $uri[2];
		
		if (array_key_exists($class, $this->handlers)) {
			if (method_exists($this->handlers[$class], $method)) {
				return $this->handlers[$class]->{$method}();
			}
			return array('error' => array('message' => "Method '$method' could not be found."));
		}
		return array('error' => array('message' => "Namespace '$class' could not be found."));
	}
}