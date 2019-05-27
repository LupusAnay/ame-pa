<?php


class ErrorResponse extends Response
{
    public function __construct(int $code, $description, array $headers)
    {
        parent::__construct($code, $description, $headers);
    }
}