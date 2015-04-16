<?php
namespace JSONAPI;

use pocketmine\plugin\PluginBase;

use JSONAPI\JSONAPIServer;
use JSONAPI\Chat;
use JSONAPI\Player;
use JSONAPI\Server;
use JSONAPI\World;

class JSONAPI extends PluginBase
{
	private enabled = true;
	private handlers = array(
		'chat' => new Chat($this),
		'player' => new Player($this),
		'server' => new Server($this),
		'world' => new World($this)
	);
	
	public function onLoad()
	{
		$httpServer = new JSONAPIServer($this, $this->getConfig()->get('host'), $this->getConfig()->get('port'));
		$httpServer->run_forever();
		
		$this->getLogger()->info('JSONAPI is initialized!');
	}

	public function onEnable()
	{
		$this->getLogger()->info('JSONAPI is enabled!');
	}

	public function onDisable()
	{
		$this->getLogger()->info('JSONAPI is disabled!');
	}
	
	public function canHandleRequest($request)
	{
		return preg_match('#^/api/#i', $request->uri);
	}
	
	public function handleRequest($request)
	{
		$uri = preg_match('#^/api/([a-z]+)#i', $request->uri);
		$class = $uri[1];
		$method = $uri[1];
		
		if (array_key_exists($class, $this->handlers) {
			if (method_exists($this->handlers[$class], $method)) {
				return $this->handlers[$class]->{$method}();
			}
			return array('error' => array('message' => "Method '$method' could not be found."));
		}
		return array('error' => array('message' => "Namespace '$class' could not be found."));
	}
}