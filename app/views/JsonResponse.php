<?php


class JsonResponse extends Response
{
    public function __construct(int $code, array $body)
    {
        parent::__construct($code, json_encode($body), ["Content-Type" => "application/json"]);
    }
}