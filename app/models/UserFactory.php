<?php


class UserFactory
{
    /**
     * @param $data
     * @return User
     * @throws ValidationError
     */
    public function make_with_password_hashing($data) {
        $this->validate_user_data($data);
        $data[User::PASSWORD] = password_hash($data[User::PASSWORD], PASSWORD_DEFAULT);
        return $this->make($data);
    }

    /**
     * @param $data
     * @throws ValidationError
     */
    private function validate_user_data($data) {
        if (!array_key_exists(User::PASSWORD, $data)) {
            throw new ValidationError("Field 'password' is required");
        }
        if (!array_key_exists(User::EMAIL, $data)) {
            throw new ValidationError("Field 'email' is required");
        }
    }

    /**
     * @param $data
     * @return User
     * @throws ValidationError
     */
    public function make($data) {
        $this->validate_user_data($data);
        $user = new User($data[User::EMAIL], $data[User::PASSWORD]);
        if (array_key_exists(User::LOGIN, $data)) {
            $user->setLogin($data[User::LOGIN]);
        }
        if (array_key_exists(User::NICKNAME, $data)) {
            $user->setNickname($data[User::NICKNAME]);
        }
        if (array_key_exists(User::IMAGE, $data)) {
            $user->setImage($data[User::IMAGE]);
        }
        return $user;
    }
}