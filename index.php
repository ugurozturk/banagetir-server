<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;

use Phalcon\Http\Response;

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
    function ($id) use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Bayiler WHERE bayi_id = :id:";

        $bayi = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        )->getFirst();



        // Create a response
        $response = new Response();

        if ($bayi === false) {
            $response->setJsonContent(
                [
                    "status" => "NOT-FOUND"
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "FOUND",
                    "data"   => [
                        "id"   => $bayi->bayi_id,
                        "name" => $bayi->bayi_adi
                    ]
                ]
            );
        }

        return $response;
    }
);

// Yeni bir bayi ekle
$app->post(
    "/api/bayiler",
    function () use ($app) {

        $bayi = $app->request->getJsonRawBody();

        $phql = "INSERT INTO Models\\Verilerim\\Bayiler 
        (bayi_kullaniciadi, bayi_sifre, bayi_adi, bayi_tel, bayi_email, bayi_adres, bayi_adreskodu, vergi_numarasi, aktif, kayit_tarihi) VALUES 
        (:bayi_kullaniciadi:, :bayi_sifre:, :bayi_adi:, :bayi_tel:, :bayi_email:, :bayi_adres:, :bayi_adreskodu:, :vergi_numarasi:, :aktif:, :kayit_tarihi:)";


        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "bayi_kullaniciadi" => $bayi->bayi_kullaniciadi,
                "bayi_sifre"   => $bayi->bayi_sifre,
                "bayi_adi" => $bayi->bayi_adi,
                "bayi_tel"   => $bayi->bayi_tel,
                "bayi_email" => $bayi->bayi_email,
                "bayi_adres"   => $bayi->bayi_adres,
                "bayi_adreskodu" => $bayi->bayi_adreskodu,
                "vergi_numarasi"   => $bayi->vergi_numarasi,
                "aktif" => $bayi->aktif,
                "kayit_tarihi"   => $bayi->kayit_tarihi
            ]
        );

        // Create a response
        $response = new Response();

        // Check if the insertion was successful
        if ($status->success() === true) {
            // Change the HTTP status
            $response->setStatusCode(201, "Created");

            $bayi->bayi_id = $status->getModel()->bayi_id;

            $response->setJsonContent(
                [
                    "status" => "OK",
                    "data"   => $bayi,
                ]
            );
        } else {
            // Change the HTTP status
            $response->setStatusCode(409, "Conflict");

            // Send errors to the client
            $errors = [];

            foreach ($status->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }

            $response->setJsonContent(
                [
                    "status"   => "ERROR",
                    "messages" => $errors,
                ]
            );
        }

        return $response;

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