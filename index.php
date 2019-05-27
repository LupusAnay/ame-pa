<?php

date_default_timezone_set('Europe/Moscow');
require 'vendor/autoload.php';

function app_factory()
{
    $app = Base::instance();
    $app->config('app/config.ini');
    $app->config('app/routes.ini');

    $raw_database_url = getenv('CLEARDB_DATABASE_URL');
    if (!$raw_database_url) {
        $raw_database_url = $app->get('database_url');
    }

    $database_url = parse_url($raw_database_url);
    $host = $database_url['host'];
    $user = $database_url['user'];
    $password = $database_url['pass'];
    $port = $database_url['port'];
    $name = ltrim($database_url["path"],'/');

    $database = new DB\SQL("mysql:host=$host;port=$port;dbname=$name", $user, $password);

    $app->set('db', $database);
    return $app;
}

app_factory()->run();
