<?php


class User extends AbstractModel
{
    private $login;
    private $email;
    private $nickname;
    private $image;
    private $password;

    const LOGIN = 'login';
    const NICKNAME = 'nickname';
    const EMAIL = 'email';
    const IMAGE = 'image';
    const PASSWORD = 'password';

    public function __construct($email, $password)
    {
        parent::__construct();

        $this->email = $email;
        $this->password = $password;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getNickname()
    {
        return $this->nickname;
    }

    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function is_password_valid($password)
    {
        return password_verify($password, $this->password);
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login): void
    {
        $this->login = $login;
    }
}