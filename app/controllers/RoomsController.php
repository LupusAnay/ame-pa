<?php


class RoomsController extends AbstractController
{
    private $rooms_factory;
    private $rooms_repository;
    private $groups_repository;
    private $groups_factory;
    private $participant_repository;
    private $participant_factory;
    private $message_repository;
    private $message_factory;

    public function __construct(Base $app)
    {
        parent::__construct($app);

        $this->rooms_factory = new RoomFactory();
        $this->rooms_repository = RepositoryFactory::get_factory()->get_room_repository();

        $this->message_repository = RepositoryFactory::get_factory()->get_message_repository();
        $this->message_factory = new MessageFactory();

        $this->groups_repository = RepositoryFactory::get_factory()->get_groups_repository();
        $this->groups_factory = new GroupFactory();

        $this->participant_repository = RepositoryFactory::get_factory()->get_participant_repository();
        $this->participant_factory = new ParticipantFactory();
    }

    public function create_room()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();
            $room = $this->rooms_factory->make($this->get_body_data());
            $group = $room->getGroup();

            if ($this->user_has_access_to_group($this->current_user, $group)) {
                $this->rooms_repository->create($room);
                $participant = $this->participant_factory->make_from_user_room($this->current_user, $room);
                $this->participant_repository->create($participant);
                return new JsonResponse(200, ["id" => $room->getId()]);
            } else {
                return new JsonErrorResponse(401, "You don't have permission to do this");
            }
        });
    }

    public function update_room()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();
            $room = $this->rooms_factory->make($this->get_body_data());
            $room->setId($this->get_room_id_param());
            if ($this->user_can_change_room($this->current_user, $room)) {
                $this->rooms_repository->update($room);
                return new EmptyResponse();
            } else {
                return new JsonErrorResponse(401, "You don't have permission to do this");
            }
        });
    }

    public function get_rooms()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();
            $rooms = $this->rooms_repository->get_user_rooms($this->current_user);
            return new JsonResponse(200, $rooms);
        });
    }

    public function delete_room()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();
            $room = $this->rooms_factory->make($this->get_body_data());
            $room->setId($this->get_room_id_param());
            if ($this->user_can_change_room($this->current_user, $room)) {
                $this->rooms_repository->delete($room);
                return new EmptyResponse();
            } else {
                return new JsonErrorResponse(401, "You don't have permission to do this");
            }
        });
    }

    public function create_message()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();
            $room = $this->rooms_repository->find_room_by_id($this->get_room_id_param());
            $participant = $this->participant_repository->find_participant($this->current_user, $room);
            $message = $this->message_factory->make($participant, $this->get_body_data());
            $this->message_repository->create($message);

            return new JsonResponse(200, ['id' => $message->getId()]);
        });
    }

    public function delete_message()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();
            $message = $this->message_repository->find_message_by_id($this->get_message_id_param());
            if ($this->user_can_change_message($this->current_user, $message)) {
                $this->message_repository->delete($message);
                return new EmptyResponse();
            } else {
                return new JsonErrorResponse(401, "You don't have permission to do this");
            }
        });
    }

    public function update_message()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();
            $message = $this->message_repository->find_message_by_id($this->get_message_id_param());
            var_dump($message);
            if ($this->user_can_change_message($this->current_user, $message)) {
                $message->update($this->get_body_data());
                $this->message_repository->update($message);
                return new EmptyResponse();
            } else {
                return new JsonErrorResponse(401, "You don't have permission to do this");
            }
        });
    }

    public function get_messages()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();
            $room = $this->rooms_repository->find_room_by_id($this->get_room_id_param());
            if ($this->user_has_access_to_room($this->current_user, $room)) {
                $messages = $this->message_repository->get_room_messages($room);
                return new JsonResponse(200, $messages);
            } else {
                return new JsonErrorResponse(401, "You don't have permission to do this");
            }
        });
    }

    /**
     * @param User $user
     * @param Room $room
     * @return bool
     * @throws InternalServerError
     * @throws NotFoundError
     */
    private function user_can_change_room(User $user, Room $room): bool
    {
        $participant = $this->participant_repository->find_participant($user, $room);
        $status = $participant->getStatus();
        if ($status != Participant::STATUS_CREATOR or $status != Participant::STATUS_MODERATOR) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param User $user
     * @param Group $group
     * @return bool
     * @throws InternalServerError
     */
    private function user_has_access_to_group(User $user, Group $group): bool
    {
        $groups = $this->groups_repository->get_user_groups($user);
        $groups += $this->groups_repository->get_public_groups();

        return in_array($group, $groups);
    }

    private function get_room_id_param(): string
    {
        return $this->app->get("PARAMS.room_id");
    }

    private function get_message_id_param()
    {
        return $this->app->get("PARAMS.message_id");
    }

    private function user_can_change_message(User $user, Message $message)
    {
        $same_user = $message->getParticipant()->getUser() == $user;
        return $same_user;
    }

    private function user_has_access_to_room(User $user, Room $room): bool
    {
        try {
            $this->participant_repository->find_participant($user, $room);
        } catch (InternalServerError $e) {
            return false;
        } catch (NotFoundError $e) {
            return false;
        }
        return true;
    }
}