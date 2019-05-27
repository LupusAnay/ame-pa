<?php


class Message extends AbstractModel
{
    private $participant;
    private $text;
    private $status;
    private $timestamp;

    const PARTICIPANT_ID = 'participant_id';
    const ROOM_ID = 'room_id';
    const TEXT = 'text';
    const STATUS = 'status';
    const TIMESTAMP = 'timestamp';

    const STATUS_SENT = 'SENT';
    const STATUS_EDITED = 'EDITED';

    public function __construct($id, Participant $participant, string $text, $status)
    {
        parent::__construct($id);

        $this->id = $id;
        $this->participant = $participant;
        $this->text = $text;
        $this->status = $status ? $status : self::STATUS_SENT;
    }

    public function getParticipant()
    {
        return $this->participant;
    }

    public function setParticipant($participant): void
    {
        $this->participant = $participant;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text): void
    {
        $this->text = $text;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function setTimestamp($timestamp): void
    {
        $this->timestamp = $timestamp;
    }
}