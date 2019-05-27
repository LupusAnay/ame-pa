<?php


use DB\SQL;

class UserRepository
{
    private $db;
    private $mapper;
    private $user_factory;

    const USER_TABLE = 'users';

    public function __construct(SQL $database)
    {
        $this->db = $database;
        $this->mapper = new SQL\Mapper($this->db, self::USER_TABLE);
        $this->user_factory = new UserFactory();
    }

    /**
     * @param User $user
     * @throws ConflictError
     */
    public function create(User $user)
    {
        if ($this->is_user_exists($user)) {
            throw new ConflictError('User with this email already exists');
        }
        $id = $user->getId();
        if ($id) {
            $this->mapper->load(["id" => $user->getId()]);
        }

        $this->mapper->copyfrom($user->__dict());
        $this->mapper->save();
        $user->setId($this->mapper->id);
    }

    /**
     * @param User $user
     * @throws NotFoundError
     */
    public function update(User $user)
    {
        if (!$user->getId() or !$this->is_user_exists($user)) {
            throw new NotFoundError('User does not exists');
        }
        $this->mapper->load(["id" => $user->getId()]);
        $this->mapper->copyfrom($user->__dict());
        $this->mapper->save();
    }

    public function delete(User $user)
    {
        $this->mapper->load(["id" => $user->getId()]);
        $this->mapper->erase();
    }

    /**
     * @param int $id
     * @return User
     * @throws InternalServerError
     * @throws NotFoundError
     */
    public function find_user_by_id(int $id)
    {
        $this->mapper->reset();
        $this->mapper->load(["id=?", $id]);
        if ($this->mapper->dry()) {
            throw new NotFoundError("User with id $id not found");
        }

        return $this->create_user_from_mapper();
    }

    /**
     * @param string $login
     * @return User
     * @throws NotFoundError
     * @throws InternalServerError
     */
    public function find_user_by_login(string $login)
    {
        $this->mapper->reset();
        $this->mapper->load([User::LOGIN => $login]);
        if ($this->mapper->dry()) {
            throw new NotFoundError("User with login $login not found");
        }
        return $this->create_user_from_mapper();
    }

    /**
     * @return User
     * @throws InternalServerError
     */
    private function create_user_from_mapper()
    {
        try {
            $data = $this->mapper->cast();
            $user = $this->user_factory->make($data);
            $user->setId($this->mapper->id);
            return $user;
        } catch (ValidationError $e) {
            throw new InternalServerError("Database error, couldn't retrieve data from mapper");
        }
    }

    public function is_user_exists(User $user)
    {
        $result = $this->db->exec(
            'select * from users where email = ?',
            [$user->getEmail()]
        );

        return (bool)$result;
    }
}