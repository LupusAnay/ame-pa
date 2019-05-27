<?php

date_default_timezone_set('Europe/Moscow');
require 'vendor/autoload.php';

function app_factory()
{
    $app = \Base::instance();
    $app->config('app/config.ini');
    $app->config('app/routes.ini');

    $database = new DB\SQL
    (
        $app->get('db_type') . ':host=' . $app->get('db_host') . ';port=' . $app->get('db_port') . ';dbname=' . $app->get('db_name'),
        $app->get('db_login'),
        $app->get('db_password')
    );

    $app->set('db', $database);
    return $app;
}

app_factory()->run();
