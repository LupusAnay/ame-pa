<?php

class ParticipantFactory
{

    /**
     * @param array $data
     * @return Participant
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function make(array $data): Participant
    {
        $this->validateParticipantData($data);

        $user_repo = RepositoryFactory::get_factory()->get_user_repository();
        $room_repo = RepositoryFactory::get_factory()->get_room_repository();

        $user = $user_repo->find_user_by_id($data[Participant::USER_ID]);
        $room = $room_repo->find_room_by_id($data[Participant::ROOM_ID]);

        $participant = new Participant(
            null,
            $room,
            $user,
            $data[Participant::NICKNAME],
            $data[Participant::STATUS]
        );
        return $participant;
    }

    public function make_from_user_room(User $user, Room $room)
    {
        $status = Participant::STATUS_USER;
        $participant = new Participant(null, $room, $user, $user->getNickname(), $status);
        return $participant;
    }

    /**
     * @param array $data
     * @return array
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function make_from_rows(array $data): array
    {
        $participants = [];
        foreach ($data as $row) {
            $participant = $this->make($row);
            $participant->setId($row[Participant::ID]);
            array_push($participants, $participant);
        }
        return $participants;
    }

    /**
     * @param array $data
     * @throws ValidationError
     */
    private function validateParticipantData(array $data)
    {
        if (!array_key_exists(Participant::USER_ID, $data)) {
            throw new ValidationError("Field 'user_id' is required");
        }

        if (!array_key_exists(Participant::ROOM_ID, $data)) {
            throw new ValidationError("Field 'room_id' is required");
        }
    }
}