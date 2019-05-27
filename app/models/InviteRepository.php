<?php


use DB\SQL;

class InviteRepository
{
    const TABLE_NAME = 'invitations';

    private $db;
    private $mapper;
    private $invite_factory;

    public function __construct(SQL $database)
    {
        $this->db = $database;
        $this->mapper = new SQL\Mapper($this->db, self::TABLE_NAME);
        $this->invite_factory = new InviteFactory();
    }

    /**
     * @param Invite $invite
     * @throws ConflictError
     */
    public function create(Invite $invite)
    {
        if ($this->is_invite_exists($invite)) {
            throw new ConflictError('Invite already exists');
        }
        $id = $invite->getId();

        if ($id) {
            $this->mapper->load([Invite::ID => $id]);
        }

        $this->mapper->group_id = $invite->getGroup()->getId();
        $this->mapper->user_id = $invite->getUser()->getId();
        $this->mapper->status = $invite->getStatus();
        $this->mapper->save();

        $invite->setId($this->mapper->id);
    }

    /**
     * @param Invite $invite
     * @throws NotFoundError
     */
    public function update(Invite $invite)
    {
        if (!$invite->getId() or !$this->is_invite_exists($invite)) {
            throw new NotFoundError('Invite does not exists');
        }

        $this->db->exec(
            'update invitations set status = ? where id = ?',
            [$invite->getStatus(), $invite->getId()]
        );
    }

    public function delete(Invite $invite)
    {
        $this->db->exec(
            'delete from invitations where id = ?',
            [$invite->getId()]
        );
    }

    /**
     * @param User $user
     * @return array
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function get_user_invites(User $user)
    {
        $result = $this->db->exec(
            'select * from invitations where user_id=?',
            [$user->getId()]
        );

        $invites = [];
        foreach ($result as $row) {
            $invite = $this->invite_factory->make($row);
            $invite->setId($row[Invite::ID]);
            array_push($invites, $invite);
        }

        return $invites;
    }

    private function is_invite_exists(Invite $invite)
    {
        $result = $this->db->exec(
            'select * from invitations where group_id = ? and user_id = ?',
            [$invite->getGroup()->getId(), $invite->getUser()->getId()]
        );

        return (bool)$result;
    }
}