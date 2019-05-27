<?php


class Participant extends AbstractModel
{
    const USER_ID = 'user_id';
    const ROOM_ID = 'room_id';
    const NICKNAME = 'nickname';
    const STATUS = 'status';

    const STATUS_CREATOR = 'CREATOR';
    const STATUS_MODERATOR = 'MODERATOR';
    const STATUS_USER = 'USER';

    private $user;
    private $room;
    private $nickname;
    private $status;

    public function __construct($id, Room $room, User $user, string $nickname, string $status)
    {
        parent::__construct($id);

        $this->room = $room;
        $this->user = $user;
        $this->nickname = $nickname;
        $this->status = $status;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user): void
    {
        $this->user = $user;
    }

    public function getRoom()
    {
        return $this->room;
    }

    public function setRoom($room): void
    {
        $this->room = $room;
    }

    public function getNickname()
    {
        return $this->nickname;
    }

    public function setNickname($nickname): void
    {
        $this->nickname = $nickname;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }
}