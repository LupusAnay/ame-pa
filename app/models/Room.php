<?php


class Room extends AbstractModel
{
    private $name;
    private $group;

    const NAME = 'name';
    const GROUP_ID = 'group_id';

    public function __construct($id, string $name, Group $group)
    {
        parent::__construct($id);

        $this->id = $id;
        $this->name = $name;
        $this->group = $group;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function setGroup(Group $group): void
    {
        $this->group = $group;
    }
}