<?php


class InviteFactory
{
    /**
     * @param array $data
     * @return Invite
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    public function make(array $data): Invite
    {
        $this->validate($data);
        $groups_repository = RepositoryFactory::get_factory()->get_groups_repository();
        $users_repository = RepositoryFactory::get_factory()->get_user_repository();

        $group = $groups_repository->find_group_by_id($data[Invite::GROUP_ID]);
        $user = $users_repository->find_user_by_id($data[Invite::USER_ID]);

        $invite = new Invite(null, $group, $user, $data[Invite::STATUS]);
        return $invite;
    }

    public function make_creator_invite(User $user, Group $group)
    {
        $invite = new Invite(null, $group, $user, Invite::STATUS_CREATOR);
        return $invite;
    }

    /**
     * @param $data
     * @throws ValidationError
     */
    public function validate($data)
    {
        if (!array_key_exists(Invite::USER_ID, $data)) {
            throw new ValidationError("Field 'user_id' is required");
        }
        if (!array_key_exists(Invite::GROUP_ID, $data)) {
            throw new ValidationError("Field 'user_id' is required");
        }
    }
}