<?php


use DB\SQL;

class ParticipantRepository
{
    const TABLE_NAME = 'participants';
    private $db;
    private $mapper;
    private $participant_factory;

    public function __construct(SQL $database)
    {
        $this->db = $database;
        $this->mapper = new SQL\Mapper($this->db, self::TABLE_NAME);
        $this->participant_factory = new ParticipantFactory();
    }

    /**
     * @param Participant $participant
     * @throws ConflictError
     */
    public function create(Participant $participant)
    {
        if ($this->is_participant_exists($participant)) {
            throw new ConflictError('This participant entry is already exists');
        }
        $this->mapper->user_id = $participant->getUser()->getId();
        $this->mapper->room_id = $participant->getRoom()->getId();
        $this->mapper->nickname = $participant->getNickname();
        $this->mapper->status = $participant->getStatus();
        $this->mapper->save();
        $participant->setId($this->mapper->id);
    }

    /**
     * @param Participant $participant
     * @throws NotFoundError
     */
    public function update(Participant $participant)
    {
        if (!$participant->getId() or !$this->is_participant_exists($participant)) {
            throw new NotFoundError('Participant does not exists');
        }

        $this->db->exec(
            'update participants set nickname = ?, status = ? where id = ?',
            [$participant->getNickname(), $participant->getStatus(), $participant->getId()]
        );
    }

    public function delete(Participant $participant)
    {
        $this->db->exec(
            'delete from participants where id = ?',
            [$participant->getId()]
        );
    }

    /**
     * @param int $id
     * @return Participant
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function find_participant_by_id(int $id): Participant
    {
        $result = $this->db->exec(
            'select * from participants where id = ?',
            [$id]
        );

        $participant = $this->participant_factory->make($result[0]);
        $participant->setId($result[0][Participant::ID]);
        return $participant;
    }

    /**
     * @param User $user
     * @param Room $room
     * @return Participant
     * @throws InternalServerError
     * @throws NotFoundError
     */
    public function find_participant(User $user, Room $room): Participant
    {
        $result = $this->db->exec(
            'select * from participants where user_id = ? and room_id = ?',
            [$user->getId(), $room->getId()]
        );

        try {
            $participant = $this->participant_factory->make($result[0]);
            $participant->setId($result[0][Participant::ID]);
        } catch (ValidationError $e) {
            throw new InternalServerError('Database data integrity corrupted', 0, $e);
        }
        return $participant;
    }

    /**
     * @param Room $room
     * @return array
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function get_room_participants(Room $room): array
    {
        $result = $this->db->exec(
            'select * from participants where room_id = ?',
            [$room->getId()]
        );

        $participants = $this->participant_factory->make_from_rows($result);
        return $participants;
    }

    private function is_participant_exists(Participant $participant): bool
    {
        $result = $this->db->exec(
            'select * from participants where id = ?',
            [$participant->getId()]
        );
        return (bool)$result;
    }
}