<?php
require_once dirname(__DIR__) . '/httpserver/httpserver.php';

class JSONAPIServer extends HTTPServer
{
    private $api
    
    function __construct($api, $port)
    {
        parent::__construct(array(
            'port' => $port,
        ));
        
        $this->api = $api;
    }

    function route_request($request)
    {
        if ($this->canHandleRequest($request))
        {
            $response = $this->handleRequest($request);
            return $this->response(200, json_encode($response), array('Content-Type' => 'application/json'));
        }
        
        return $this->response(400, json_encode(array('error' => array('message' => 'Request could not be handled.'))), array('Content-Type' => 'application/json'), 'Bad Request');
    }
}