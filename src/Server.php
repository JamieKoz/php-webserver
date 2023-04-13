<?php

namespace WebServer;
use WebServer\Request;
use WebServer\Response;
use WebServer\Exception;
class Server {
    protected $host = null;
    protected $port = null;
    protected $socket = null;

    // creates a socket
    public function createSocket(){
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
    }

    // binds the socket to the host and port
    public function bind(){
        if(!socket_bind($this->socket, $this->host, $this->port)){
            throw new Exception('Could not bind to socket'. $this->host . ':' . $this->port . ' - ' . socket_strerror( socket_last_error() ) );
        }
    }

    // 
    public function __construct($host, $port){
        $this->host = $host;
        $this->port = (int) $port;

        $this->createSocket();

        $this->bind();
    }

    // checks if callback is valid, otherwise throws an exception
    public function listen($callback){

        if(!is_callable($callback)){
            throw new Exception('The given argument is not callable.');
        }

        while(true){
            // listen for connections
            socket_listen($this->socket);

            // accept connections, if there is no connection, close the socket
            if(!$client = socket_accept($this->socket)){
                socket_close( $client );
                continue;
            }

            // create a new request instance with the clients header
            $request = new Request::withHeaderString(socket_read($client, 1024));

            // execute the callback
            $response = call_user_func($callback, $request);

            // check if received a response object
            // else return a 404
            if(!$response || !$response instanceof Response){
                $response = new Response(404, 'Not Found');
            }

            // make a string out of the response
            $response = (string) $response;

            // write the response to the client
            socket_write($client, $response, strlen($response));

            // close the client socket
            socket_close( $client );
        }
    }
}