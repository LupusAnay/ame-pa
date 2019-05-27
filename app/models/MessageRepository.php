<?php


use DB\SQL;

class MessageRepository
{
    const TABLE_NAME = 'messages';
    private $db;
    private $mapper;
    private $message_factory;

    public function __construct(SQL $database)
    {
        $this->db = $database;
        $this->mapper = new SQL\Mapper($this->db, self::TABLE_NAME);
        $this->message_factory = new MessageFactory();
    }

    public function create(Message $message)
    {
        $message->setTimestamp(date("Y-m-d H:i:s"));
        $this->mapper->participant_id = $message->getParticipant()->getId();
        $this->mapper->text = $message->getText();
        $this->mapper->status = $message->getStatus();
        $this->mapper->timestamp = $message->getTimestamp();
        $this->mapper->save();
        $message->setId($this->mapper->id);
    }

    /**
     * @param Message $message
     * @throws NotFoundError
     */
    public function update(Message $message)
    {
        if (!$message->getId() or !$this->is_message_exists($message)) {
            throw new NotFoundError('Message does not exists');
        }

        $this->db->exec(
            'update messages set text = ?, status = ?, timestamp = ? where id = ?',
            [$message->getText(), $message->getStatus(), $message->getTimestamp(), $message->getId()]
        );
    }

    private function is_message_exists(Message $message): bool
    {
        $result = $this->db->exec(
            'select * from messages where id = ?',
            [$message->getId()]
        );
        return (bool)$result;
    }

    public function delete(Message $message)
    {
        $this->db->exec(
            'delete from messages where id = ?',
            [$message->getId()]
        );
    }

    /**
     * @param int $id
     * @return Message
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function find_message_by_id(int $id): Message
    {
        $result = $this->db->exec(
            'select * from messages where id = ?',
            [$id]
        );

        $message = $this->message_factory->make_with_participant_id($result[0]);
        $message->setId($result[0][Message::ID]);
        return $message;
    }

    /**
     * @param Room $room
     * @param User|null $user
     * @return array
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function get_room_messages(Room $room, User $user = null): array
    {
        if ($user) {
            $result = $this->db->exec(
                'select m.id, participant_id, text, m.status, timestamp
                        from messages m
                        join participants p on m.participant_id = p.id and p.room_id = ? and p.user_id = ?',
                [$room->getId(), $user->getId()]
            );
        } else {
            $result = $this->db->exec(
                'select m.id, participant_id, text, m.status, timestamp
                        from messages m
                        join participants p on m.participant_id = p.id and p.room_id = ?',
                [$room->getId()]
            );
        }

        $messages = $this->message_factory->make_from_rows($result);
        return $messages;
    }
}