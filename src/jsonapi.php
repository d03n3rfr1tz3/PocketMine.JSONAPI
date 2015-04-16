<?php

/*
  __PocketMine Plugin__
  name=JSONAPI
  description=A simple JSONAPI.
  version=1.0
  author=Dirk Sarodnick
  class=JSONAPI
  apiversion=12
 */

require_once dirname(__DIR__) . '/httpserver.php';

class JSONAPI implements Plugin {

    private $api, $config;

    public function __construct(ServerAPI $api, $server = false) {
        $this->api = $api;
    }

    public function init() {
        $this->config = new Config($this->api->plugin->configPath($this) . "config.yml", CONFIG_YAML, array(
            "port" => 19133,
        ));
        
        $httpServer = new JSONAPIServer($this, $this->config->port);
        $httpServer->run_forever();
    }

    public function eventHandler($data, $event) {
        switch (strtolower($event)) {
            default:
                return true;
        }
    }

    public function commandHandler($cmd, $params, $issuer) {
        switch (strtolower($cmd)) {
            default:
        }
    }
    
    public function canHandleRequest($request) {
        
    }
    
    public function handleRequest($request) {
        
    }

    public function __destruct() {
        
    }

}
