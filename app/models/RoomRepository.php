<?php


use DB\SQL;

class RoomRepository
{
    private $db;
    private $mapper;
    private $room_factory;

    const TABLE_NAME = 'rooms';

    public function __construct(SQL $database)
    {
        $this->db = $database;
        $this->mapper = new SQL\Mapper($this->db, self::TABLE_NAME);
        $this->room_factory = new RoomFactory();
    }

    public function create(Room $room)
    {
        $this->mapper->group_id = $room->getGroup()->getId();
        $this->mapper->name = $room->getName();
        $this->mapper->save();
        $room->setId($this->mapper->id);
    }

    /**
     * @param Room $room
     * @throws NotFoundError
     */
    public function update(Room $room)
    {
        if (!$room->getId() or !$this->is_room_exists($room)) {
            throw new NotFoundError('Group does not exists');
        }

        $this->db->exec(
            'update rooms set name = ?, group_id = ? where id = ?',
            [$room->getName(), $room->getGroup()->getId(), $room->getId()]
        );
    }

    public function delete(Room $room)
    {
        $this->db->exec(
            'delete from rooms where id = ?',
            [$room->getId()]
        );
    }

    /**
     * @param int $id
     * @return Room
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function find_room_by_id(int $id): Room
    {
        $result = $this->db->exec(
            'select * from rooms where id = ?',
            [$id]
        );
        $room = $this->room_factory->make($result[0]);
        return $room;
    }

    /**
     * @param User $user
     * @return array
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function get_user_rooms(User $user): array
    {
        $result = $this->db->exec(
            'select r.id, r.name, r.group_id from 
                                     rooms r join 
                                         participants p on r.id = p.room_id and p.user_id = ?',
            [$user->getId()]
        );
        $rooms = $this->room_factory->make_from_rows($result);
        return $rooms;
    }

    /**
     * @param Group $group
     * @return array
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function get_group_rooms(Group $group)
    {
        $result = $this->db->exec(
            'select * from rooms where group_id = ?',
            [$group->getId()]
        );
        $rooms = $this->room_factory->make_from_rows($result);
        return $rooms;
    }

    private function is_room_exists(Room $room)
    {
        $result = $this->db->exec(
            'select * from rooms where id = ?',
            [$room->getId()]
        );
        return (bool)$result;
    }
}