<?php


use DB\SQL;

class GroupRepository
{
    const TABLE_NAME = 'groups';
    private $db;
    private $mapper;
    private $group_factory;

    public function __construct(SQL $database)
    {
        $this->db = $database;
        $this->mapper = new SQL\Mapper($this->db, self::TABLE_NAME);
        $this->group_factory = new GroupFactory();
    }

    public function create(Group $group)
    {
        $this->db->exec(
            'insert into `groups` values (?, ?, ?)',
            [$group->getId(), $group->getName(), $group->getStatus()]
        );

        $group->setId($group->getId());
    }

    /**
     * @param User $user
     * @return array
     * @throws InternalServerError
     */
    public function get_user_groups(User $user): array
    {
        $result = $this->db->exec(
            'select g.id, g.name, g.status from `groups` g join users on users.id=?',
            [$user->getId()]
        );
        $groups = [];
        foreach ($result as $row) {
            try {
                $group = $this->group_factory->make($row);
            } catch (ValidationError $e) {
                throw new InternalServerError('Integrity error: cannot create group from db data');
            }
            $group->setId($row[Group::ID]);
            array_push($groups, $group);
        }

        return $groups;
    }

    /**
     * @return array
     * @throws InternalServerError
     */
    public function get_public_groups(): array
    {
        $this->mapper->load(["status=?", Group::STATUS_PUBLIC]);
        $groups = [];
        try {
            while (!$this->mapper->dry()) {
                $group_data = $this->mapper->cast();
                $group = $this->group_factory->make($group_data);
                $group->setId($this->mapper->id);

                array_push($groups, $group);
                $this->mapper->next();
            }
        } catch (ValidationError $e) {
            throw new InternalServerError('Integrity error: cannot create group from db data');
        }
        return $groups;
    }

    /**
     * @param string $group_id
     * @return Group
     * @throws InternalServerError
     * @throws NotFoundError
     */
    public function find_group_by_id(string $group_id)
    {
        $this->mapper->reset();
        $id = Group::ID;
        $this->mapper->load(["$id=?", $group_id]);
        if ($this->mapper->dry()) {
            throw new NotFoundError("Group with id $group_id not found");
        }

        return $this->create_group_from_mapper();
    }

    /**
     * @return Group
     * @throws InternalServerError
     */
    private function create_group_from_mapper()
    {
        try {
            $data = $this->mapper->cast();
            $group = $this->group_factory->make($data);
            $group->setId($this->mapper->id);
            return $group;
        } catch (ValidationError $e) {
            throw new InternalServerError("Database error, couldn't retrieve data from mapper");
        }
    }

    /**
     * @param Group $group
     * @throws NotFoundError
     */
    public function update(Group $group)
    {
        if (!$group->getId() or !$this->is_group_exists($group)) {
            throw new NotFoundError('Group does not exists');
        }

        $this->db->exec(
            'update `groups` set name = ?, status = ? where id = ?',
            [$group->getName(), $group->getStatus(), $group->getId()]
        );
    }

    private function is_group_exists(Group $group): bool
    {
        $result = $this->db->exec(
            'select * from `groups` where id = ?',
            [$group->getId()]
        );
        return (bool)$result;
    }

    public function delete(Group $group)
    {
        $this->db->exec(
            'delete from `groups` where id = ?',
            [$group->getId()]
        );
    }
}