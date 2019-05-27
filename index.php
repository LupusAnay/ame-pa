<?php

date_default_timezone_set('Europe/Moscow');


function app_factory()
{
    $app = require('lib/base.php');

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
