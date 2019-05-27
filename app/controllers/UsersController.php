<?php


class UsersController extends AbstractController
{
    private $repository;
    private $user_factory;

    public function __construct(Base $app)
    {
        parent::__construct($app);

        $this->repository = RepositoryFactory::get_factory()->get_user_repository();
        $this->user_factory = new UserFactory();
    }

    public function create_user()
    {
        $this->make_response(function () {
            $data = $this->get_body_data();
            $user = $this->user_factory->make_with_password_hashing($data);
            $this->repository->create($user);
            return new JsonResponse(200, ['id' => $user->getId()]);
        });
    }

    public function login()
    {
        $this->make_response(function () {
            $data = $this->get_body_data();

            if (!array_key_exists(User::EMAIL, $data)) {
                return new JsonErrorResponse(422, "field 'email' is missing");
            }
            if (!array_key_exists(User::PASSWORD, $data)) {
                return new JsonErrorResponse(422, "field 'password' is missing");
            }

            $user = $this->repository->find_user_by_login($data[User::EMAIL]);
            if ($user->is_password_valid($data[User::PASSWORD])) {
                $this->start_session($user);
                return new EmptyResponse();
            } else {
                return new JsonErrorResponse(401, 'wrong password');
            }
        });
    }

    public function logout()
    {
        $this->make_response(function () {
            $this->stop_session();
            return new EmptyResponse();
        });
    }

    public function update_user()
    {
        $this->make_response(function () {
            $this->authorized_or_forbidden();
            $data = $this->get_body_data();
            $this->current_user->update($data);
            $this->repository->update($this->current_user);
            return new EmptyResponse();
        });
    }

    public function delete_user()
    {
        $this->make_response(function () {
            if (!$this->current_user) {
                return new JsonErrorResponse(403, 'Authentication required');
            }

            $this->repository->delete($this->current_user);
            $this->stop_session();
            return new EmptyResponse();
        });
    }
}