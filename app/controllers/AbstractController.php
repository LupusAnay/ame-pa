<?php


abstract class AbstractController
{
    const SESSION_LOGGED_IN = 'SESSION.logged_in';
    const SESSION_USER_ID = 'SESSION.user_id';

    protected $db;
    protected $app;
    protected $current_user;
    private $response;


    /**
     * AbstractController constructor.
     * @param Base $app
     * @throws InternalServerError
     * @throws NotFoundError
     */
    public function __construct(Base $app)
    {
        $this->app = $app;
        $this->db = $this->app->get('db');
        RepositoryFactory::set_database($this->db);
        $this->response = new JsonErrorResponse(418, 'Server did not respond properly');

        if ($this->is_logged_in()) {
            $this->current_user = $this->get_user_from_session();
        }
    }

    protected function make_response(callable $method)
    {
        try {
            $result = $method();
            if($result)
                $this->respond($result);
            else
                $this->respond(new EmptyResponse());
        } catch (NotFoundError $e) {
            $this->response = new JsonErrorResponse(404, $e->getMessage());
        } catch (ValidationError $e) {
            $this->respond(new JsonErrorResponse(422, $e->getMessage()));
        } catch (InternalServerError $e) {
            $this->respond(new JsonErrorResponse(500, $e->getMessage()));
        } catch (ForbiddenError $e) {
            $this->respond(new JsonErrorResponse(401, $e->getMessage()));
        } catch (ConflictError $e) {
            $this->respond(new JsonErrorResponse(409, $e->getMessage()));
        }
    }

    protected function is_logged_in()
    {
        if (!$this->app->get(self::SESSION_LOGGED_IN)) {
            return false;
        }

        return true;
    }

    /**
     * @return User
     * @throws InternalServerError
     * @throws NotFoundError
     */
    protected function get_user_from_session()
    {
        $repository = RepositoryFactory::get_factory()->get_user_repository();
        $user_id = $this->app->get(self::SESSION_USER_ID);
        try {
            return $repository->find_user_by_id($user_id);
        } catch (InternalServerError $e) {
            $this->stop_session();
            throw $e;
        } catch (NotFoundError $e) {
            $this->stop_session();
            throw $e;
        }
    }

    private function respond(Response $response)
    {
        $this->response = $response;
    }

    public function __destruct()
    {
        $this->response->make_response();
    }

    protected function start_session(User $user)
    {
        $this->app->set(self::SESSION_LOGGED_IN, true);
        $this->app->set(self::SESSION_USER_ID, $user->getId());
    }

    protected function stop_session()
    {
        $this->app->clear('SESSION');
    }

    /**
     * @return mixed
     * @throws ValidationError
     */
    protected function get_body_data() {
        $data = $this->app->get('BODY');
        if (!$data) {
            throw new ValidationError("body should not be empty");
        }
        return json_decode($data, true);
    }

    /**
     * @throws ForbiddenError
     */
    protected function authorized_or_forbidden() {
        if (!$this->is_logged_in()) {
            throw new ForbiddenError('Authentication required');
        }
    }
}