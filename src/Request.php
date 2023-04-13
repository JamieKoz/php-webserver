<?php

namespace WebServer;

class Request{
    protected $method = null;
    protected $uri = null;
    protected $parameters = [];
    protected $headers = [];


    public function __construct($method, $uri, $headers = []){
        $this->method = strtoupper($method);
        $this->headers = $headers;

        // split the uri and the parameters string
        @list($this->uri, $params) = explode('?', $uri);

        // parse the parameters string into an array
        parse_str($params, $this->parameters);  
    }

    public static function withHeaderString( $header ){

        // split the header string into an array
        $lines = explode("\n", $header);

        // extract the method and uri from the first line
        list($method, $uri) = explode(' ', array_shift($lines));
        
        $headers = [];

        foreach($lines as $line){
            // remove whitespaces and clean the line
            $line = trim($line);
            if(strpos($line, ':') !== false){
                list($key, $value) = explode(':', $line);
                $headers[$key] = $value;
            }
        }

        // create a new request instance
        return new static ($method, $uri, $headers);
    }
    
    public function method(){
        return $this->method;
    }

    public function uri(){
        return $this->uri;
    }

    public function param($key, $default = null){
        if(isset($this->parameters[$key])){
            return $default;
        }

        return $this->parameters[$key];
    }
}