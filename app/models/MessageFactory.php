<?php


class MessageFactory
{

    /**
     * @param array $data
     * @return array
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function make_from_rows(array $data): array
    {
        $messages = [];
        foreach ($data as $row) {
            $message = $this->make_with_participant_id($row);
            $message->setId($row[Message::ID]);
            array_push($messages, $message);
        }
        return $messages;
    }

    /**
     * @param array $data
     * @return Message
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function make_with_participant_id(array $data)
    {
        $this->validateMessageData($data);

        $participant_repo = RepositoryFactory::get_factory()->get_participant_repository();
        $participant = $participant_repo->find_participant_by_id($data[Message::PARTICIPANT_ID]);

        $message = new Message(null, $participant, $data[Message::TEXT], $data[Message::STATUS]);
        $message->setTimestamp($data[Message::TIMESTAMP]);
        return $message;
    }

    /**
     * @param Participant $participant
     * @param array $data
     * @return Message
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function make(Participant $participant, array $data)
    {
        $data[Message::PARTICIPANT_ID] = $participant->getId();
        return $this->make_with_participant_id($data);
    }

    /**
     * @param array $data
     * @throws ValidationError
     */
    private function validateMessageData(array $data)
    {
        if (!array_key_exists(Message::PARTICIPANT_ID, $data)) {
            throw new ValidationError("Field 'participant_id' is required");
        }
        if (!array_key_exists(Message::TEXT, $data)) {
            throw new ValidationError("Field 'text' is required");
        }
    }
}