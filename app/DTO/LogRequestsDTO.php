<?php

namespace App\DTO;

class LogRequestsDTO
{
    public $url;
    public $method;
    public $controller;
    public $controller_method;
    public $request_body;
    public $request_headers;
    public $user_id;
    public $user_ip;
    public $user_agent;
    public $response_status;
    public $response_body;
    public $response_headers;
    public $called_at;

    public function __construct($data)
    {
        $this->url = $data['url'];
        $this->method = $data['method'];
        $this->controller = $data['controller'];
        $this->controller_method = $data['controller_method'];
        $this->request_body = $data['request_body'];
        $this->request_headers = $data['request_headers'];
        $this->user_id = $data['user_id'];
        $this->user_ip = $data['user_ip'];
        $this->user_agent = $data['user_agent'];
        $this->response_status = $data['response_status'];
        $this->response_body = $data['response_body'];
        $this->response_headers = $data['response_headers'];
        $this->called_at = $data['called_at'];
    }
}
