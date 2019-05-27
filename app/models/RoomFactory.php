<?php


class RoomFactory
{
    /**
     * @param $data
     * @return Room
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function make(array $data): Room {
        $this->validateGroupData($data);

        $id = array_key_exists(Room::ID, $data) ? $data[Room::ID] : null;
        $group_repo = RepositoryFactory::get_factory()->get_groups_repository();
        $group = $group_repo->find_group_by_id($data[Room::GROUP_ID]);
        $room = new Room($id, $data[Room::NAME], $group);
        return $room;
    }

    /**
     * @param array $data
     * @return array
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function make_from_rows(array $data): array {
        $rooms = [];
        foreach ($data as $row) {
            $room = $this->make($row);
            $room->setId($row[Room::ID]);
            array_push($rooms, $room);
        }
        return $rooms;
    }

    /**
     * @param array $data
     * @throws ValidationError
     */
    private function validateGroupData(array $data) {
        if (!array_key_exists(Room::NAME, $data)) {
            throw new ValidationError("Field 'name' is required");
        }
        if (!array_key_exists(Room::GROUP_ID, $data)) {
            throw new ValidationError("Field 'group_id' is required");
        }
    }
}