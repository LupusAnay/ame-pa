<?php


use DB\SQL;

class RepositoryFactory
{
    private static $_instance;
    private static $_database;

    private function __construct()
    {
    }

    public static function set_factory(RepositoryFactory $f)
    {
        self::$_instance = $f;
    }

    public static function set_database(SQL $db) {
        self::$_database = $db;
    }

    public static function get_factory()
    {
        if (!self::$_instance) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function get_user_repository() {
        return new UserRepository(self::$_database);
    }

    public function get_groups_repository()
    {
        return new GroupRepository(self::$_database);
    }

    public function get_invites_repository()
    {
        return new InviteRepository(self::$_database);
    }

    public function get_room_repository()
    {
        return new RoomRepository(self::$_database);
    }

    public function get_participant_repository() {
        return new ParticipantRepository(self::$_database);
    }

    public function get_message_repository() {
        return new MessageRepository(self::$_database);
    }
}