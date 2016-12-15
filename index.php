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
// Tüm bayileri getir
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
                "bayi_tel"   => $bayi->bayi_tel,
                "bayi_email" => $bayi->bayi_email,
                "bayi_adres"   => $bayi->bayi_adres,
                "bayi_adreskodu" => $bayi->bayi_adreskodu,
                "vergi_numarasi"   => $bayi->vergi_numarasi,
                "aktif" => $bayi->aktif,
                "kayit_tarihi"   => $bayi->kayit_tarihi
            ];
        }

        echo json_encode($data);
    }
);

// Bayi Adresinda Arama Yap
$app->get(
    "/api/bayiler/search/{name}",
    function ($name) use ($app) {

        $phql = "SELECT * FROM Models\\Verilerim\\Bayiler WHERE bayi_kullaniciadi LIKE :name: OR bayi_adi LIKE :name:";

        $bayiler = $app->modelsManager->executeQuery(
            $phql,
            [
                "name" => "%" . $name . "%"
            ]);

        $data = [];

        foreach ($bayiler as $bayi) {
            $data[] = [
                "bayi_id"   => $bayi->bayi_id,
                "bayi_adi" => $bayi->bayi_adi,
                "bayi_tel"   => $bayi->bayi_tel,
                "bayi_email" => $bayi->bayi_email,
                "bayi_adres"   => $bayi->bayi_adres,
                "bayi_adreskodu" => $bayi->bayi_adreskodu,
                "vergi_numarasi"   => $bayi->vergi_numarasi,
                "aktif" => $bayi->aktif,
                "kayit_tarihi"   => $bayi->kayit_tarihi
            ];
        }

        echo json_encode($data);

    }
);

// Primary Keye bağlı bayiyi getir
$app->get(
    "/api/bayiler/{id:[0-9]+}",
    function ($id) {

    }
);

// Yeni bir bayi ekle
$app->post(
    "/api/bayiler",
    function () {

    }
);

// Bayi id sine bağlı güncelle
$app->put(
    "/api/bayiler/{id:[0-9]+}",
    function () {

    }
);

// Primary key e göre sil
$app->delete(
    "/api/bayiler/{id:[0-9]+}",
    function () {

    }
);

$app->handle();