<?php


class JsonErrorResponse extends JsonResponse
{
    public function __construct(int $code, $description)
    {
        parent::__construct($code, ['error' => $description]);
    }
}