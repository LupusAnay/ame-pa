<?php


class GroupsController extends AbstractController
{
    private $groups_repository;
    private $group_factory;
    private $invite_repository;
    private $invite_factory;
    private $room_repository;
    private $room_factory;
    private $participant_repository;
    private $participant_factory;

    public function __construct(Base $app)
    {
        parent::__construct($app);

        $this->groups_repository = RepositoryFactory::get_factory()->get_groups_repository();
        $this->group_factory = new GroupFactory();

        $this->invite_repository = RepositoryFactory::get_factory()->get_invites_repository();
        $this->invite_factory = new InviteFactory();

        $this->room_repository = RepositoryFactory::get_factory()->get_room_repository();
        $this->room_factory = new RoomFactory();

        $this->participant_repository = RepositoryFactory::get_factory()->get_participant_repository();
        $this->participant_factory = new ParticipantFactory();
    }

    public function create_group()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();

            $group = $this->group_factory->make($this->get_body_data());
            $this->groups_repository->create($group);

            $invite = $this->invite_factory->make_creator_invite($this->current_user, $group);
            $this->invite_repository->create($invite);

            return new JsonResponse(200, ['id' => $group->getId()]);
        });
    }

    public function get_groups()
    {
        $this->make_response(function () {
            if (!$this->is_logged_in()) {
                $groups = $this->groups_repository->get_public_groups();
            } else {
                $groups = $this->groups_repository->get_public_groups();
                $groups += $this->groups_repository->get_user_groups($this->current_user);
            }
            return new JsonResponse(200, $groups);
        });
    }

    public function update_group()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();
            $group = $this->groups_repository->find_group_by_id($this->get_group_id_param());
            if ($this->is_user_admin($group, $this->current_user)) {
                $group->update($this->get_body_data());
                $this->groups_repository->update($group);
                return new EmptyResponse();
            } else {
                return new JsonErrorResponse(401, "You don't have permission to do this");
            }
        });
    }

    private function get_group_id_param(): string
    {
        return $this->app->get('PARAMS.group_id');
    }

    /**
     * @param $group
     * @param $user
     * @return bool
     * @throws InternalServerError
     * @throws NotFoundError
     * @throws ValidationError
     */
    private function is_user_admin($group, $user): bool
    {
        $user_invites = $this->invite_repository->get_user_invites($user);
        $user_invites = array_filter($user_invites, function (Invite $inv) use ($group) {
            return $inv->getGroup() == $group;
        });
        $statuses = array_map(function (Invite $inv) {
            return $inv->getStatus();
        }, $user_invites);
        $is_moderator = in_array(Invite::STATUS_MODERATOR, $statuses);
        $is_creator = in_array(Invite::STATUS_CREATOR, $statuses);
        return $is_moderator or $is_creator;
    }

    public function delete_group()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();
            $group = $this->groups_repository->find_group_by_id($this->get_group_id_param());
            if ($this->is_user_admin($group, $this->current_user)) {
                $this->groups_repository->delete($group);
                return new EmptyResponse();
            } else {
                return new JsonErrorResponse(401, "You don't have permission to do this");
            }
        });
    }

    public function add_user()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();

            $data = $this->get_body_data();
            $invite = $this->invite_factory->make($data);

            if ($this->is_user_admin($invite->getGroup(), $this->current_user)) {
                $this->invite_repository->create($invite);

                $rooms = $this->room_repository->get_group_rooms($invite->getGroup());
                foreach ($rooms as $room) {
                    $participant = $this->participant_factory->make_from_user_room($invite->getUser(), $room);
                    $this->participant_repository->create($participant);
                }

                return new JsonResponse(200, ["id" => $invite->getId()]);
            } else {
                return new JsonErrorResponse(401, "You don't have permission to do this");
            }
        });
    }

    public function remove_user()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();

            $invite_data = $this->get_body_data();
            $invite = $this->invite_factory->make($invite_data);

            if ($this->is_user_admin($invite->getGroup(), $this->current_user)) {
                $this->invite_repository->delete($invite);
                return new EmptyResponse();
            } else {
                return new JsonErrorResponse(401, "You don't have permission to do this");
            }
        });
    }

    public function change_user_status()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();

            $invite_data = $this->get_body_data();
            $invite = $this->invite_factory->make($invite_data);

            if ($this->is_user_admin($invite->getGroup(), $this->current_user)) {
                $this->invite_repository->update($invite);
                return new EmptyResponse();
            } else {
                return new JsonErrorResponse(401, "You don't have permission to do this");
            }
        });
    }
}