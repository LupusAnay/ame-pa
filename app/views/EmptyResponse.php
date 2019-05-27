<?php


class EmptyResponse extends Response
{
    public function __construct()
    {
        parent::__construct(204, [], []);
    }
}