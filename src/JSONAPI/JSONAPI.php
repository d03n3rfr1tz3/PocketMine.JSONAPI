<?php
namespace JSONAPI;

use pocketmine\plugin\PluginBase;

use JSONAPI\JSONAPIServer;
use JSONAPI\JSONAPITask;
use JSONAPI\api\PlayerHandler;
use JSONAPI\api\ServerHandler;
use JSONAPI\api\WorldHandler;

class JSONAPI extends PluginBase
{
	private $handlers;
	private $server;
	private $task;
	private $logins;
	
	function __construct()
	{
		$this->handlers = array(
			new PlayerHandler($this),
			new ServerHandler($this),
			new WorldHandler($this)
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
		$this->logins = $this->getConfig()->get('logins');
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
	
	public function getHandles() {
		$handles = array();
		foreach ($this->handlers as $handler) {
			foreach ($handler->handles() as $handle) {
				$handles[$handle] = $handler;
			}
		}
		return $handles;
	}
	
	public function canHandleRequest($request)
	{
		return preg_match('#^/api/call#i', $request->uri) &&
		(
			($request->method == "GET" && preg_match('#&?json=#i', $request->query_string)) ||
			($request->method == "POST" && $request->content_len > 0)
		);
	}
	
	public function handleRequest($request)
	{
		parse_str($request->query_string, $params);
		
		$requestJson = $request->method == "GET" ? $params['json'] : fread($request->content_stream, $request->content_len);
		$requestJsonParsed = json_decode($requestJson, true);
		$requestObjects = (array_keys($requestJsonParsed) !== range(0, count($requestJsonParsed) - 1)) ? array($requestJsonParsed) : $requestJsonParsed;
		
		$responseObjects = array();
		foreach ($requestObjects as $requestObject) {
			$authenticated = array_key_exists('username', $requestObject) && array_key_exists('key', $requestObject);
			if (!$authenticated) return array('error' => array('message' => "You are not authenticated."));
			
			$method = $user = array_key_exists('name', $requestObject) ? $requestObject['name'] : '';
			$user = array_key_exists('username', $requestObject) ? $requestObject['username'] : '';
			$key = array_key_exists('key', $requestObject) ? $requestObject['key'] : '';
			$pass = $this->logins[$user];
			
			$authorized = $key == hash('sha256', $user.$method.$pass);
			if (!$authorized) return array('error' => array('message' => "User '$user' is not authorized."));
			
			$handles = $this->getHandles();
			if (!array_key_exists($method, $handles)) return array('error' => array('message' => "Method '{$requestObject['name']}' could not be found."));
			
			$responseObject = $handles[$method]->handle($method, array_key_exists('arguments', $requestObject) ? $requestObject['arguments'] : null);
			array_push($responseObjects, array('success' => $responseObject));
		}
		
		return $responseObjects;
	}
}