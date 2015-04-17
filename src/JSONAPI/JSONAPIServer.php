<?php
namespace JSONAPI;

use JSONAPI\httpserver\HTTPServer;

class JSONAPIServer extends HTTPServer
{
	private $owner;
	
	function __construct($plugin)
	{
		$this->owner = $plugin;
		
		$config = $plugin->getConfig();
		parent::__construct(array(
			'host' => $config->get('host'),
			'port' => $config->get('port')
		));
	}
	
	function listening()
	{
		$this->getOwner()->getLogger()->info("JSONAPI listening on {$this->addr}:{$this->port}.");
	}
	
	function route_request($request)
	{
		$plugin = $this->getOwner();
		if (!$plugin->isEnabled()) $this->response(503, '', null, 'Service Unavailable');
		
		if ($plugin->canHandleRequest($request))
		{
			$response = $plugin->handleRequest($request);
			return $this->response(200, json_encode($response), array('Content-Type' => 'application/json'));
		}
		
		return $this->response(400, json_encode(array('error' => array('message' => 'Request could not be handled.'))), array('Content-Type' => 'application/json'), 'Bad Request');
	}
	
	private function getOwner() {
		return $this->owner;
	}
}