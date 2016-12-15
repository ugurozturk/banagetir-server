<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;

// Use Loader() to autoload our model
$loader = new Loader();

$loader->registerNamespaces(
    [
        "Models\\Verilerim" => __DIR__ . "/models/",
    ]
);

$loader->register();

$di = new FactoryDefault();

// Set up the database service
$di->set(
    "db",
    function () {
        return new PdoMysql(
            [
                "host"     => "localhost",
                "username" => "root",
                "password" => "",
                "dbname"   => "banagetir",
            ]
        );
    }
);

// Create and bind the DI to the application
$app = new Micro($di);
// Retrieves all robots
$app->get(
    "/api/bayiler",
    function () use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Bayiler";

        $bayiler = $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($bayiler as $bayi) {
            $data[] = [
                "bayi_id"   => $bayi->bayi_id,
                "bayi_adi" => $bayi->bayi_adi,
            ];
        }

        echo json_encode($data);
    }
);

// Searches for robots with $name in their name
$app->get(
    "/api/robots/search/{name}",
    function ($name) {

    }
);

// Retrieves robots based on primary key
$app->get(
    "/api/robots/{id:[0-9]+}",
    function ($id) {

    }
);

// Adds a new robot
$app->post(
    "/api/robots",
    function () {

    }
);

// Updates robots based on primary key
$app->put(
    "/api/robots/{id:[0-9]+}",
    function () {

    }
);

// Deletes robots based on primary key
$app->delete(
    "/api/robots/{id:[0-9]+}",
    function () {

    }
);

$app->handle();