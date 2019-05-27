<?php


class Response
{
    public $status_code;
    public $body;
    public $headers;

    public function __construct(int $code, $body, array $headers)
    {
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }

        http_response_code($code);

        $this->body = $body;
    }

    public function make_response()
    {
        echo $this->body;
    }
}