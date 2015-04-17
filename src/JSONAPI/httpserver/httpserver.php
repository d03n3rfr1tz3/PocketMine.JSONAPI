<?php
namespace JSONAPI\httpserver;

/*
 * A simple standalone HTTP server for development that serves PHP scripts and static files.
 * Clients should subclass HTTPServer and override the route_request() method, at least.
 *
 * Copyright (c) 2011, Trust for Conservation Innovation
 * Released under MIT license; see LICENSE.txt
 * http://github.com/youngj/httpserver
 */
 
use JSONAPI\httpserver\HTTPRequest;
use JSONAPI\httpserver\HTTPResponse;

class HTTPServer
{
	/* 
	 * The following public properties can be passed as options to the constructor: 
	 */    
	public $addr = '0.0.0.0';               // IP address to listen on
	public $port = 80;                      // TCP port number to listen on    
	private $sock;

	/* 
	 * Internal map of active client socket resource IDs to HTTPRequest objects
	 */    
	private $requests = array(/* socket_id => HTTPRequest */);    

	/* 
	 * Internal map of stream resource IDs to HTTPResponse objects 
	 * (only includes HTTPResponse objects with an associated stream)
	 */        
	private $responses = array(/* stream_id => HTTPResponse */);    

	function __construct($options = null)
	{
		if ($options)
		{
			foreach ($options as $k => $v)
			{
				$this->$k = $v;
			}
		}
	}

	/*  
	 * Subclasses should override to route the current request to either a static file or PHP script
	 * and return a HTTPResponse object. This function should call get_static_response() or
	 * get_php_response(), as applicable.
	 */
	function route_request($request)
	{
		return $this->text_response(500, "HTTPServer::route_request not implemented");
	}    

	/*  
	 * Subclasses can override to get started event
	 */
	function listening()
	{
		$port = $this->port;
		echo "HTTP server listening on {$this->addr}:$port (see http://localhost:$port/)...\n";    
	}

	/*
	 * Subclasses could override to disallow other characters in path names
	 */
	function is_allowed_uri($uri)
	{        
		return $uri[0] == '/'                   // all URIs should start with a /
			&& strpos($uri, '..') === false     // prevent paths from escaping document root
			&& !preg_match('#/\.#', $uri);      // disallow dotfiles
	}    

	/*
	 * Subclasses could override to output a log entry in a particular format
	 */    
	function get_log_line($request)
	{
		$response = $request->response;        
		$time = strftime("%H:%M:%S");
		
		// http://www.w3.org/Daemon/User/Config/Logging.html#common-logfile-format
		return "{$request->remote_addr} - - [$time] \"{$request->request_line}\" {$response->status} {$response->bytes_written}\n";
	}      

	/*
	 * Subclasses could override for logging or other other post-request events
	 */    
	function request_done($request)
	{
		echo $this->get_log_line($request);
	}      

	function bind_error($errno, $errstr)
	{
		error_log("Could not start a web server on port {$this->port}: {$errstr}");    
	}

	function run_once($startup = false)
	{
		if ($startup) {
			$this->sock = stream_socket_server("tcp://{$this->addr}:{$this->port}", $errno, $errstr, STREAM_SERVER_LISTEN | STREAM_SERVER_BIND);
			
			if (!$this->sock)
			{
				$this->bind_error($errno, $errstr);
				return;
			}
			
			stream_set_blocking($this->sock, 0);
			$this->listening();
		}
		
		$requests =& $this->requests;
		$responses =& $this->responses;
		
		$read = array();
		$write = array();
		foreach ($requests as $id => $request)
		{
			if (!$request->is_read_complete())
			{
				$read[] = $request->socket;
			}
			else 
			{
				$response = $request->response;
				
				$buffer_len = strlen($response->buffer);
				if ($buffer_len)
				{
					$write[] = $request->socket;
				}
				
				if ($buffer_len < 20000 && !$response->stream_eof())
				{
					$read[] = $response->stream;
				}
			}
		}
		
		$read[] = $this->sock;       
		$except = null;
		if (@stream_select($read, $write, $except, 0, 0) < 1) return;
		if (in_array($this->sock, $read)) // new client connection
		{
			$client = stream_socket_accept($this->sock, 1);
			$requests[(int)$client] = new HTTPRequest($client);
			
			$key = array_search($this->sock, $read);
			unset($read[$key]);
		}
		
		foreach ($read as $stream)
		{
			if (isset($responses[(int)$stream]))
			{
				$this->read_response($stream);
			}
			else
			{
				$this->read_socket($stream);
			}
		}
		
		foreach ($write as $client)
		{
			$this->write_socket($client);
		}
	}

	function run_forever()
	{    
		// provide some required/useful environment variables even if 'E' is not in variables_order
		$env_keys = array('HOME','OS','Path','PATHEXT','SystemRoot','TEMP','TMP');
		foreach ($env_keys as $key)
		{
			$_ENV[$key] = getenv($key);
		}

		set_time_limit(0);

		$addr_port = "{$this->addr}:{$this->port}";
		
		$sock = @stream_socket_server("tcp://$addr_port", $errno, $errstr);
						  
		if (!$sock)
		{            
			$this->bind_error($errno, $errstr);
			return;
		}
		
		stream_set_blocking($sock, 0);     

		$requests =& $this->requests;
		$responses =& $this->responses;

		// send startup event
		$this->listening();

		while (true)
		{        
			$read = array();
			$write = array();
			foreach ($requests as $id => $request)
			{            
				if (!$request->is_read_complete())
				{
					$read[] = $request->socket;
				}
				else 
				{
					$response = $request->response;
					
					$buffer_len = strlen($response->buffer);
					if ($buffer_len)
					{
						$write[] = $request->socket;
					}
					
					if ($buffer_len < 20000 && !$response->stream_eof())
					{
						$read[] = $response->stream;
					}
				}                
			}            
			$read[] = $sock;       
			$except = null;
			
			if (stream_select($read, $write, $except, null) < 1)
				continue;                
						
			if (in_array($sock, $read)) // new client connection
			{
				$client = stream_socket_accept($sock);
				$requests[(int)$client] = new HTTPRequest($client);
				
				$key = array_search($sock, $read);
				unset($read[$key]);
			}
			
			foreach ($read as $stream)
			{
				if (isset($responses[(int)$stream]))
				{
					$this->read_response($stream);
				}
				else
				{
					$this->read_socket($stream);
				}
			}
			
			foreach ($write as $client)
			{
				$this->write_socket($client);
			}
		}        
	}

	function write_socket($client)
	{    
		$request = $this->requests[(int)$client];
		$response = $request->response;
		$response_buf =& $response->buffer;     
		
		$len = @fwrite($client, $response_buf);   
				
		if ($len === false)
		{
			$this->end_request($request);
		}
		else
		{
			$response->bytes_written += $len;
			$response_buf = substr($response_buf, $len);
			
			if ($response->eof())
			{                
				$this->request_done($request);
			
				if ($request->get_header('Connection') === 'close' || $request->http_version !== 'HTTP/1.1')
				{
					$this->end_request($request);
				}
				else // HTTP Keep-Alive: expect another request on same client socket
				{           
					$request->cleanup();                
					$this->end_response($response);
					$this->requests[(int)$client] = new HTTPRequest($client);
				}
			}
		}
	}

	function read_response($stream)
	{    
		$response = $this->responses[(int)$stream];
		
		$data = @fread($stream, 30000);

		if ($data !== false)
		{    
			if (isset($response->buffer[0]))
			{
				$response->buffer .= $data;
			}
			else
			{                
				$response->buffer = $data;
			}
		}
	}

	function read_socket($client)
	{
		$request = $this->requests[(int)$client];
		$data = @fread($client, 30000);
				
		if ($data === false || $data == '')
		{
			$this->end_request($request);
		}
		else
		{        
			$request->add_data($data);
			
			if ($request->is_read_complete())
			{
				$this->read_request_complete($request);
			}    
		}
	}

	function read_request_complete($request)
	{
		$uri = $request->uri;
		
		if (!$this->is_allowed_uri($uri))
		{
			$response = $this->text_response(403, "Invalid URI $uri"); 
		}
		else
		{        
			$response = $this->route_request($request);        
		}
		
		if ($response->prepend_headers)
		{
			$response->buffer = $response->render();
		}            
				
		if ($response->stream)
		{
			$this->responses[(int)$response->stream] = $response;
		}
		
		$request->set_response($response);
	}

	function end_request($request)
	{
		$request->cleanup();
		@fclose($request->socket);
		unset($this->requests[(int)$request->socket]);           
		$request->socket = null;
		
		if ($request->response)
		{
			$this->end_response($request->response);
			$request->response = null;
		}
	}        

	function end_response($response)
	{
		if ($response->stream)
		{        
			@fclose($response->stream);
			unset($this->responses[(int)$response->stream]);    
			$response->stream = null;
		}
	}

	/*
	 * Returns a generic HTTPResponse object for this server.
	 */
	function response($status = 200, $content = '', $headers = null, $status_msg = null)
	{
		$response = new HTTPResponse($status, $content, $headers, $status_msg);
		$response->headers['Server'] = 'JSONAPI v1.0';
		return $response;        
	}
	
	function text_response($status, $content)
	{
		$response = $this->response($status, $content);
		$response->headers['Content-Type'] = 'text/plain';
		return $response;
	}

	static function parse_headers($headers_str)
	{
		$headers_arr = explode("\r\n", $headers_str);
				
		$headers = array();
		foreach ($headers_arr as $header_str)
		{
			$header_arr = explode(": ", $header_str, 2);
			if (sizeof($header_arr) == 2)
			{
				$header_name = $header_arr[0];
				$value = $header_arr[1];
				
				if (!isset($headers[$header_name]))
				{
					$headers[$header_name] = array($value);
				}
				else
				{
					$headers[$header_name][] = $value;
				}
			}
		}                
		return $headers;
	}
}
