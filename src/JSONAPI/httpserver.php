<?php
namespace JSONAPI;

use httpserver\HTTPServer

class JSONAPIServer extends HTTPServer
{
	private $plugin

	function __construct($plugin, $host, $port)
	{
		parent::__construct(array(
			'host' => $host
			'port' => $port
		));
		
		$this->plugin = $plugin;
	}

	function route_request($request)
	{
		if (!$this->plugin->enabled) return;
		
		if ($this->plugin->canHandleRequest($request))
		{
			$response = $this->plugin->handleRequest($request);
			return $this->response(200, json_encode($response), array('Content-Type' => 'application/json'));
		}
		
		return $this->response(400, json_encode(array('error' => array('message' => 'Request could not be handled.'))), array('Content-Type' => 'application/json'), 'Bad Request');
	}
}