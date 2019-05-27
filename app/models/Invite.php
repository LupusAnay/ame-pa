<?php


class Invite extends AbstractModel
{
    private $group;
    private $user;
    private $status;

    const GROUP_ID = 'group_id';
    const USER_ID = 'user_id';
    const STATUS = 'status';

    const STATUS_CREATOR = 'CREATOR';
    const STATUS_USER = 'USER';
    const STATUS_MODERATOR = 'MODERATOR';
    const STATUS_READONLY = 'READONLY';

    public function __construct($id, Group $group, User $user, string $status)
    {
        parent::__construct($id);

        $this->group = $group;
        $this->user = $user;
        $this->status = $status;
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function setGroup(Group $group): void
    {
        $this->group = $group;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}