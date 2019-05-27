<?php


class Group extends AbstractModel
{
    private $name;
    private $status;

    const NAME = 'name';
    const STATUS = 'status';

    const STATUS_PRIVATE = 'PRIVATE';
    const STATUS_PUBLIC = 'PUBLIC';

    public function __construct($id, $name, $status)
    {
        parent::__construct($id);

        $this->name = $name;
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }
}