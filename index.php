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
                "charset"   => "utf8mb4"
            ]
        );
    }
);

// Create and bind the DI to the application
$app = new Micro($di);

//*******BAYİLER*********//
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


//TODO bayiyi email + bayi adı na göre arama yapsın. Alttakini düzelt veya yeni router ekle
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
                        "bayi_id"   => $bayi->bayi_id,
                        "bayi_adi" => $bayi->bayi_adi,
                        "bayi_tel"   => $bayi->bayi_tel,
                        "bayi_email" => $bayi->bayi_email,
                        "bayi_adres"   => $bayi->bayi_adres,
                        "bayi_adreskodu" => $bayi->bayi_adreskodu,
                        "vergi_numarasi"   => $bayi->vergi_numarasi,
                        "aktif" => $bayi->aktif,
                        "kayit_tarihi"   => $bayi->kayit_tarihi
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
        (bayi_kullaniciadi, bayi_sifre, bayi_adi, bayi_tel, bayi_email, bayi_adres, bayi_adreskodu, vergi_numarasi, aktif) VALUES 
        (:bayi_kullaniciadi:, :bayi_sifre:, :bayi_adi:, :bayi_tel:, :bayi_email:, :bayi_adres:, :bayi_adreskodu:, :vergi_numarasi:, :aktif:)";


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
                "aktif" => $bayi->aktif
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
    function ($id) use ($app) {
        $bayi = $app->request->getJsonRawBody();

        $db_bayi = Models\Verilerim\Bayiler::findFirst("bayi_id =" . $id);

        $response = new Response();

        if (!$db_bayi) {
             $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "message" => "Belirlenen id de değer yok"
                ]
            );
            return $response;
        }
       
        //id yi değiştirmesini engelle.
        if (isset($bayi->bayi_id)) {
            unset($bayi->bayi_id);
        }

        foreach ($bayi as $key => $value) {
            $db_bayi->$key = $value;
        }

        if ($db_bayi->save() === false) {

        $messages = $db_bayi->getMessages();
        $response->setStatusCode(409, "Conflict");
        $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages"   => $messages,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        }

        return $response;
    }
);

// Primary key e göre sil
$app->delete(
    "/api/bayiler/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "DELETE FROM Models\\Verilerim\\Bayiler WHERE bayi_id = :id:";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        );

        // Create a response
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        } else {
            // Change the HTTP status
            $response->setStatusCode(409, "Conflict");

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

//*******KATEGORİLER*********//
// Tüm kategorileri getir
$app->get(
    "/api/kategoriler",
    function () use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Kategoriler";
        $kategoriler = $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($kategoriler as $kategori) {
            $data[] = [
                "kategori_id"   => $kategori->kategori_id,
                "kategori_adi" => $kategori->kategori_adi,
                "ust_kategori_id"   => $kategori->ust_kategori_id,
            ];
        }

        echo json_encode($data);
    }
);

// Kategorileri Adresinda Arama Yap
$app->get(
    "/api/kategoriler/search/{name}",
    function ($name) use ($app) {

        $phql = "SELECT * FROM Models\\Verilerim\\Kategoriler WHERE kategori_adi LIKE :name:";

        $kategoriler = $app->modelsManager->executeQuery(
            $phql,
            [
                "name" => "%" . $name . "%"
            ]);

        $data = [];

        foreach ($kategoriler as $kategori) {
            $data[] = [
                "kategori_id"   => $kategori->kategori_id,
                "kategori_adi" => $kategori->kategori_adi,
                "ust_kategori_id"   => $kategori->ust_kategori_id,
            ];
        }

        echo json_encode($data);

    }
);

// Primary Keye bağlı kategorileri getir
$app->get(
    "/api/kategoriler/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Kategoriler WHERE kategori_id = :id:";

        $kategori = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        )->getFirst();



        // Yanıt Oluştur
        $response = new Response();

        if ($kategori === false) {
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
                        "kategori_id"   => $kategori->kategori_id,
                        "kategori_adi" => $kategori->kategori_adi,
                        "ust_kategori_id" => $kategori->ust_kategori_id
                    ]
                ]
            );
        }

        return $response;
    }
);

// Yeni bir Kategorileri ekle
$app->post(
    "/api/kategoriler",
    function () use ($app) {

        $kategori = $app->request->getJsonRawBody();

        $phql = "INSERT INTO Models\\Verilerim\\Kategoriler 
        (kategori_adi, ust_kategori_id) VALUES 
        (:kategori_adi:, :ust_kategori_id:)";


        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "kategori_adi" => $kategori->kategori_adi,
                "ust_kategori_id"   => $kategori->ust_kategori_id
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        // veri oluşturma başarılımı kontrol et
        if ($status->success() === true) {
            // Http durumunu değiştir
            $response->setStatusCode(201, "Created");

            $kategori->kategori_id = $status->getModel()->kategori_id;

            $response->setJsonContent(
                [
                    "status" => "OK",
                    "data"   => $kategori
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

            // Hataları döndürmek için
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

// Kategorileri id sine bağlı güncelle
$app->put(
    "/api/kategoriler/{id:[0-9]+}",
    function ($id) use ($app) {
        $kategori = $app->request->getJsonRawBody();

        $db_kategori = Models\Verilerim\Kategoriler::findFirst("kategori_id =" . $id);

        $response = new Response();

        if (!$db_kategori) {
             $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "message" => "Belirlenen id de değer yok"
                ]
            );
            return $response;
        }
       
        //id yi değiştirmesini engelle.
        if (isset($kategori->kategori_id)) {
            unset($kategori->kategori_id);
        }

        foreach ($kategori as $key => $value) {
            $db_kategori->$key = $value;
        }

        if ($db_kategori->save() === false) {

        $messages = $db_kategori->getMessages();
        $response->setStatusCode(409, "Conflict");
        $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages"   => $messages,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        }

        return $response;
    }
);

// Primary key e göre sil
$app->delete(
    "/api/kategoriler/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "DELETE FROM Models\\Verilerim\\Kategoriler WHERE kategori_id = :id:";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

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

//*******LogKategori*********//
// Tüm kategorileri getir
$app->get(
    "/api/logkategori",
    function () use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\LogKategori";
        $log_kategorileri = $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($log_kategorileri as $log_kategori) {
            $data[] = [
                "log_kategori_id"   => $log_kategori->log_kategori_id,
                "log_kategori_adi" => $log_kategori->log_kategori_adi,
            ];
        }

        echo json_encode($data);
    }
);

// log_kategori Adresinda Arama Yap
$app->get(
    "/api/logkategori/search/{name}",
    function ($name) use ($app) {

        $phql = "SELECT * FROM Models\\Verilerim\\LogKategori WHERE log_kategori_adi LIKE :name:";

        $log_kategorileri = $app->modelsManager->executeQuery(
            $phql,
            [
                "name" => "%" . $name . "%"
            ]);

        $data = [];

        foreach ($log_kategorileri as $log_kategori) {
            $data[] = [
                "log_kategori_id"   => $log_kategori->log_kategori_id,
                "log_kategori_adi" => $log_kategori->log_kategori_adi,
            ];
        }

        echo json_encode($data);

    }
);

// Primary Keye bağlı log_kategori getir
$app->get(
    "/api/logkategori/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\LogKategori WHERE log_kategori_id = :id:";

        $log_kategori = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        )->getFirst();



        // Yanıt Oluştur
        $response = new Response();

        if ($log_kategori === false) {
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
                        "log_kategori_id"   => $log_kategori->log_kategori_id,
                        "log_kategori_adi" => $log_kategori->log_kategori_adi,
                    ]
                ]
            );
        }

        return $response;
    }
);

// Yeni bir log_kategori ekle
$app->post(
    "/api/logkategori",
    function () use ($app) {

        $log_kategori = $app->request->getJsonRawBody();

        $phql = "INSERT INTO Models\\Verilerim\\LogKategori 
        (log_kategori_adi) VALUES 
        (:log_kategori_adi:)";


        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "log_kategori_adi" => $log_kategori->log_kategori_adi,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        // veri oluşturma başarılımı kontrol et
        if ($status->success() === true) {
            // Http durumunu değiştir
            $response->setStatusCode(201, "Created");

            $log_kategori->log_kategori_id = $status->getModel()->log_kategori_id;

            $response->setJsonContent(
                [
                    "status" => "OK",
                    "data"   => $log_kategori
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

            // Hataları döndürmek için
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

// log_kategori id sine bağlı güncelle
$app->put(
    "/api/logkategori/{id:[0-9]+}",
    function ($id) use ($app) {
        $log_kategori = $app->request->getJsonRawBody();

        $db_kategori = Models\Verilerim\LogKategori::findFirst("log_kategori_id =" . $id);

        $response = new Response();

        if (!$db_kategori) {
             $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "message" => "Belirlenen id de değer yok"
                ]
            );
            return $response;
        }
       
        //id yi değiştirmesini engelle.
        if (isset($log_kategori->log_kategori_id)) {
            unset($log_kategori->log_kategori_id);
        }

        foreach ($log_kategori as $key => $value) {
            $db_kategori->$key = $value;
        }

        if ($db_kategori->save() === false) {

        $messages = $db_kategori->getMessages();
        $response->setStatusCode(409, "Conflict");
        $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages"   => $messages,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        }

        return $response;
    }
);

// Primary key e göre sil
$app->delete(
    "/api/logkategori/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "DELETE FROM Models\\Verilerim\\LogKategori WHERE log_kategori_id = :id:";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

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

//*******Marka*********//
// Tüm kategorileri getir
$app->get(
    "/api/marka",
    function () use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Marka";
        $markalar = $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($markalar as $marka) {
            $data[] = [
                "marka_id"   => $marka->marka_id,
                "marka_adi" => $marka->marka_adi,
            ];
        }

        echo json_encode($data);
    }
);

// marka Adresinda Arama Yap
$app->get(
    "/api/marka/search/{name}",
    function ($name) use ($app) {

        $phql = "SELECT * FROM Models\\Verilerim\\Marka WHERE marka_adi LIKE :name:";

        $markalar = $app->modelsManager->executeQuery(
            $phql,
            [
                "name" => "%" . $name . "%"
            ]);

        $data = [];

        foreach ($markalar as $marka) {
            $data[] = [
                "marka_id"   => $marka->marka_id,
                "marka_adi" => $marka->marka_adi,
            ];
        }

        echo json_encode($data);

    }
);

// Primary Keye bağlı marka getir
$app->get(
    "/api/marka/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Marka WHERE marka_id = :id:";

        $marka = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        )->getFirst();

        // Yanıt Oluştur
        $response = new Response();

        if ($marka === false) {
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
                        "marka_id"   => $marka->marka_id,
                        "marka_adi" => $marka->marka_adi,
                    ]
                ]
            );
        }

        return $response;
    }
);

// Yeni bir marka ekle
$app->post(
    "/api/marka",
    function () use ($app) {

        $marka = $app->request->getJsonRawBody();

        $phql = "INSERT INTO Models\\Verilerim\\Marka 
        (marka_adi) VALUES 
        (:marka_adi:)";


        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "marka_adi" => $marka->marka_adi,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        // veri oluşturma başarılımı kontrol et
        if ($status->success() === true) {
            // Http durumunu değiştir
            $response->setStatusCode(201, "Created");

            $marka->marka_id = $status->getModel()->marka_id;

            $response->setJsonContent(
                [
                    "status" => "OK",
                    "data"   => $marka
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

            // Hataları döndürmek için
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

// marka id sine bağlı güncelle
$app->put(
    "/api/marka/{id:[0-9]+}",
    function ($id) use ($app) {
        $marka = $app->request->getJsonRawBody();

        $db_marka = Models\Verilerim\Marka::findFirst("marka_id =" . $id);

        $response = new Response();

        if (!$db_marka) {
             $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "message" => "Belirlenen id de değer yok"
                ]
            );
            return $response;
        }
       
        //id yi değiştirmesini engelle.
        if (isset($marka->marka_id)) {
            unset($marka->marka_id);
        }

        foreach ($marka as $key => $value) {
            $db_marka->$key = $value;
        }

        if ($db_marka->save() === false) {

        $messages = $db_marka->getMessages();
        $response->setStatusCode(409, "Conflict");
        $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages"   => $messages,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        }

        return $response;
    }
);

// Primary key e göre sil
$app->delete(
    "/api/marka/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "DELETE FROM Models\\Verilerim\\Marka WHERE marka_id = :id:";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

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

//*******Ürünler*********//
// Tüm ürünleri getir
$app->get(
    "/api/urunler",
    function () use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Urunler";
        $urunler = $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($urunler as $urun) {
            $data[] = [
                "urun_id"   => $urun->urun_id,
                "bayi_id" => $urun->bayi_id,
                "kategori_id"   => $urun->kategori_id,
                "marka_id" => $urun->marka_id,
                "urun_adi"   => $urun->urun_adi,
                "birim_fiyat" => $urun->birim_fiyat,
                "kayit_tarihi"   => $urun->kayit_tarihi,
                "aktif" => $urun->aktif,
            ];
        }

        echo json_encode($data);
    }
);

// urunler Adresinda Arama Yap
$app->get(
    "/api/urunler/search/{name}",
    function ($name) use ($app) {

        $phql = "SELECT * FROM Models\\Verilerim\\Urunler WHERE urun_adi LIKE :name:";

        $urunler = $app->modelsManager->executeQuery(
            $phql,
            [
                "name" => "%" . $name . "%"
            ]);

        $data = [];

        foreach ($urunler as $urun) {
            $data[] = [
                "urun_id"   => $urun->urun_id,
                "bayi_id" => $urun->bayi_id,
                "kategori_id"   => $urun->kategori_id,
                "marka_id" => $urun->marka_id,
                "urun_adi"   => $urun->urun_adi,
                "birim_fiyat" => $urun->birim_fiyat,
                "kayit_tarihi"   => $urun->kayit_tarihi,
                "aktif" => $urun->aktif,
            ];
        }

        echo json_encode($data);

    }
);

//TODO test et
// Primary Keye bağlı ürünü getir
$app->get(
    "/api/urunler/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Urunler WHERE urun_id = :id:";

        $urun = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        )->getFirst();

        // Yanıt Oluştur
        $response = new Response();

        if ($urun === false) {
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
                                "urun_id"   => $urun->urun_id,
                                "bayi_id" => $urun->bayi_id,
                                "kategori_id"   => $urun->kategori_id,
                                "marka_id" => $urun->marka_id,
                                "urun_adi"   => $urun->urun_adi,
                                "birim_fiyat" => $urun->birim_fiyat,
                                "kayit_tarihi"   => $urun->kayit_tarihi,
                                "aktif" => $urun->aktif,
                    ]
                ]
            );
        }

        return $response;
    }
);

// Yeni bir ürün ekle
$app->post(
    "/api/urunler",
    function () use ($app) {

        $urun = $app->request->getJsonRawBody();

        $phql = "INSERT INTO Models\\Verilerim\\Urunler 
        (bayi_id, kategori_id, marka_id, urun_adi, birim_fiyat, aktif) VALUES 
        (:bayi_id:,:kategori_id:,:marka_id:,:urun_adi:,:birim_fiyat:,:aktif:)";

//TODO marka_id null düşülebilsin.
        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "bayi_id" => $urun->bayi_id,
                "kategori_id"   => $urun->kategori_id,
                "marka_id" => $urun->marka_id,
                "urun_adi"   => $urun->urun_adi,
                "birim_fiyat" => $urun->birim_fiyat,
                "aktif" => $urun->aktif,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        // veri oluşturma başarılımı kontrol et
        if ($status->success() === true) {
            // Http durumunu değiştir
            $response->setStatusCode(201, "Created");

            $urun->urun_id = $status->getModel()->urun_id;

            $response->setJsonContent(
                [
                    "status" => "OK",
                    "data"   => $urun
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

            // Hataları döndürmek için
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

// ürün id sine bağlı güncelle
$app->put(
    "/api/urunler/{id:[0-9]+}",
    function ($id) use ($app) {
        $urun = $app->request->getJsonRawBody();

        $db_urun = Models\Verilerim\Urunler::findFirst("urun_id =" . $id);

        $response = new Response();

        if (!$db_urun) {
             $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "message" => "Belirlenen id de değer yok"
                ]
            );
            return $response;
        }
       
        //id yi değiştirmesini engelle.
        if (isset($urun->urun_id)) {
            unset($urun->urun_id);
        }

        foreach ($urun as $key => $value) {
            $db_urun->$key = $value;
        }

        if ($db_urun->save() === false) {

        $messages = $db_urun->getMessages();
        $response->setStatusCode(409, "Conflict");
        $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages"   => $messages,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        }

        return $response;
    }
);

// Primary key e göre sil
$app->delete(
    "/api/urunler/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "DELETE FROM Models\\Verilerim\\Urunler WHERE urun_id = :id:";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

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

//*******User Groups*********//
// Tüm usergroups ları getir
$app->get(
    "/api/usergroups",
    function () use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\UserGroups";
        $usergroups = $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($usergroups as $usergroup) {
            $data[] = [
                "user_group_id"   => $usergroup->user_group_id,
                "user_group_name" => $usergroup->user_group_name,
            ];
        }

        echo json_encode($data);
    }
);

// usergroups Adresinda Arama Yap
$app->get(
    "/api/usergroups/search/{name}",
    function ($name) use ($app) {

        $phql = "SELECT * FROM Models\\Verilerim\\UserGroups WHERE user_group_name LIKE :name:";

        $usergroups = $app->modelsManager->executeQuery(
            $phql,
            [
                "name" => "%" . $name . "%"
            ]);

        $data = [];

        foreach ($usergroups as $usergroup) {
            $data[] = [
                "user_group_id"   => $usergroup->user_group_id,
                "user_group_name" => $usergroup->user_group_name,
            ];
        }

        echo json_encode($data);

    }
);

// Primary Keye bağlı ürünü getir
$app->get(
    "/api/usergroups/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\UserGroups WHERE user_group_id = :id:";

        $usergroup = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        )->getFirst();

        // Yanıt Oluştur
        $response = new Response();

        if ($usergroups === false) {
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
                                "user_group_id"   => $usergroup->user_group_id,
                                "user_group_name" => $usergroup->user_group_name,
                    ]
                ]
            );
        }

        return $response;
    }
);

// Yeni bir ürün ekle
$app->post(
    "/api/usergroups",
    function () use ($app) {

        $usergroup = $app->request->getJsonRawBody();

        $phql = "INSERT INTO Models\\Verilerim\\UserGroups 
        (user_group_name) VALUES 
        (:user_group_name:)";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "user_group_name" => $usergroup->user_group_name,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        // veri oluşturma başarılımı kontrol et
        if ($status->success() === true) {
            // Http durumunu değiştir
            $response->setStatusCode(201, "Created");

            $usergroup->user_group_id = $status->getModel()->user_group_id;

            $response->setJsonContent(
                [
                    "status" => "OK",
                    "data"   => $usergroup
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

            // Hataları döndürmek için
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

// usergroups id sine bağlı güncelle
$app->put(
    "/api/usergroups/{id:[0-9]+}",
    function ($id) use ($app) {
        $usergroup = $app->request->getJsonRawBody();

        $db_usergroup = Models\Verilerim\UserGroups::findFirst("user_group_id =" . $id);

        $response = new Response();

        if (!$db_usergroup) {
             $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "message" => "Belirlenen id de değer yok"
                ]
            );
            return $response;
        }
       
        //id yi değiştirmesini engelle.
        if (isset($usergroup->user_group_id)) {
            unset($usergroup->user_group_id);
        }

        foreach ($usergroup as $key => $value) {
            $db_usergroup->$key = $value;
        }

        if ($db_usergroup->save() === false) {

        $messages = $db_usergroup->getMessages();
        $response->setStatusCode(409, "Conflict");
        $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages"   => $messages,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        }

        return $response;
    }
);

// Primary key e göre sil
$app->delete(
    "/api/usergroups/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "DELETE FROM Models\\Verilerim\\UserGroups WHERE user_group_id = :id:";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

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

//*******Users*********//
// Tüm users ları getir
$app->get(
    "/api/users",
    function () use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Users";
        $users = $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($users as $user) {
            $data[] = [
                "user_id"   => $user->user_id,
                "user_group_id" => $user->user_group_id,
                "user_kullaniciadi"   => $user->user_kullaniciadi,
                "user_sifre" => $user->user_sifre,
                "user_adi"   => $user->user_adi,
                "user_soyadi" => $user->user_soyadi,
                "user_tel"   => $user->user_tel,
                "user_email" => $user->user_email,
                "user_dogumtarihi"   => $user->user_dogumtarihi,
                "user_adres" => $user->user_adres,
                "user_adreskodu"   => $user->user_adreskodu,
                "aktif" => $user->aktif,
                "kayit_tarihi"   => $user->kayit_tarihi,
            ];
        }

        echo json_encode($data);
    }
);

// users Adresinda Arama Yap
$app->get(
    "/api/users/search/{name}",
    function ($name) use ($app) {

        $phql = "SELECT * FROM Models\\Verilerim\\Users WHERE user_kullaniciadi LIKE :name: OR user_adi LIKE :name: OR user_soyadi LIKE :name:";

        $users = $app->modelsManager->executeQuery(
            $phql,
            [
                "name" => "%" . $name . "%"
            ]);

        $data = [];

        foreach ($users as $user) {
            $data[] = [
                "user_id"   => $user->user_id,
                "user_group_id" => $user->user_group_id,
                "user_kullaniciadi"   => $user->user_kullaniciadi,
                "user_sifre" => $user->user_sifre,
                "user_adi"   => $user->user_adi,
                "user_soyadi" => $user->user_soyadi,
                "user_tel"   => $user->user_tel,
                "user_email" => $user->user_email,
                "user_dogumtarihi"   => $user->user_dogumtarihi,
                "user_adres" => $user->user_adres,
                "user_adreskodu"   => $user->user_adreskodu,
                "aktif" => $user->aktif,
                "kayit_tarihi"   => $user->kayit_tarihi,
            ];
        }

        echo json_encode($data);

    }
);

// Primary Keye bağlı ürünü getir
$app->get(
    "/api/users/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Users WHERE user_id = :id:";

        $user = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        )->getFirst();

        // Yanıt Oluştur
        $response = new Response();

        if ($user === false) {
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
                                "user_id"   => $user->user_id,
                                "user_group_id" => $user->user_group_id,
                                "user_kullaniciadi"   => $user->user_kullaniciadi,
                                "user_sifre" => $user->user_sifre,
                                "user_adi"   => $user->user_adi,
                                "user_soyadi" => $user->user_soyadi,
                                "user_tel"   => $user->user_tel,
                                "user_email" => $user->user_email,
                                "user_dogumtarihi"   => $user->user_dogumtarihi,
                                "user_adres" => $user->user_adres,
                                "user_adreskodu"   => $user->user_adreskodu,
                                "aktif" => $user->aktif,
                                "kayit_tarihi"   => $user->kayit_tarihi,
                    ]
                ]
            );
        }

        return $response;
    }
);

// Yeni bir user ekle
$app->post(
    "/api/users",
    function () use ($app) {

        $user = $app->request->getJsonRawBody();

        $phql = "INSERT INTO Models\\Verilerim\\Users 
        (user_group_id,user_kullaniciadi,user_sifre,user_adi,user_soyadi,user_tel,user_email,user_dogumtarihi,user_adres,user_adreskodu,aktif) VALUES 
        (:user_group_id:,:user_kullaniciadi:,:user_sifre:,:user_adi:,:user_soyadi:,:user_tel:,:user_email:,:user_dogumtarihi:,:user_adres:,:user_adreskodu:,:aktif:)";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "user_group_id" => $user->user_group_id,
                "user_kullaniciadi"   => $user->user_kullaniciadi,
                "user_sifre" => $user->user_sifre,
                "user_adi"   => $user->user_adi,
                "user_soyadi" => $user->user_soyadi,
                "user_tel"   => $user->user_tel,
                "user_email" => $user->user_email,
                "user_dogumtarihi"   => $user->user_dogumtarihi,
                "user_adres" => $user->user_adres,
                "user_adreskodu"   => $user->user_adreskodu,
                "aktif" => $user->aktif,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        // veri oluşturma başarılımı kontrol et
        if ($status->success() === true) {
            // Http durumunu değiştir
            $response->setStatusCode(201, "Created");

            $user->user_id = $status->getModel()->user_id;

            $response->setJsonContent(
                [
                    "status" => "OK",
                    "data"   => $user
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

            // Hataları döndürmek için
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

// user id sine bağlı güncelle
$app->put(
    "/api/users/{id:[0-9]+}",
    function ($id) use ($app) {
        $user = $app->request->getJsonRawBody();

        $db_user= Models\Verilerim\Users::findFirst("user_id =" . $id);

        $response = new Response();

        if (!$db_user) {
             $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "message" => "Belirlenen id de değer yok"
                ]
            );
            return $response;
        }
       
        //id yi değiştirmesini engelle.
        if (isset($user->user_id)) {
            unset($user->user_id);
        }

        foreach ($user as $key => $value) {
            $db_user->$key = $value;
        }

        if ($db_user->save() === false) {

        $messages = $db_user->getMessages();
        $response->setStatusCode(409, "Conflict");
        $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages"   => $messages,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        }

        return $response;
    }
);

// Primary key e göre sil
$app->delete(
    "/api/users/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "DELETE FROM Models\\Verilerim\\Users WHERE user_id = :id:";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

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

//*******Logs*********//
// Tüm logları getir
$app->get(
    "/api/logs",
    function () use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Logs";
        $logs = $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($logs as $log) {
            $data[] = [
                "log_id"   => $log->log_id,
                "log_kategori_id" => $log->log_kategori_id,
                "log_detail" => $log->log_detail
            ];
        }

        echo json_encode($data);
    }
);

// logların Adresinda Arama Yap
$app->get(
    "/api/logs/search/{name}",
    function ($name) use ($app) {

        $phql = "SELECT * FROM Models\\Verilerim\\Logs WHERE log_detail LIKE :name:";

        $log_kategorileri = $app->modelsManager->executeQuery(
            $phql,
            [
                "name" => "%" . $name . "%"
            ]);

        $data = [];

        foreach ($log_kategorileri as $log_kategori) {
            $data[] = [
                "log_id"   => $log->log_id,
                "log_kategori_id" => $log->log_kategori_id,
                "log_detail" => $log->log_detail
            ];
        }

        echo json_encode($data);

    }
);

// Primary Keye bağlı log getir
$app->get(
    "/api/logs/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Logs WHERE log_id = :id:";

        $log = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        )->getFirst();


        $response = new Response();

        if ($log === false) {
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
                        "log_id"   => $log->log_id,
                        "log_kategori_id" => $log->log_kategori_id,
                        "log_detail" => $log->log_detail
                    ]
                ]
            );
        }

        return $response;
    }
);

// Yeni bir log ekle
$app->post(
    "/api/logs",
    function () use ($app) {

        $log = $app->request->getJsonRawBody();

        $phql = "INSERT INTO Models\\Verilerim\\Logs 
        (log_kategori_id,log_detail) VALUES 
        (:log_kategori_id:,:log_detail:)";


        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "log_kategori_id" => $log->log_kategori_id,
                "log_detail" => $log->log_detail
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        // veri oluşturma başarılımı kontrol et
        if ($status->success() === true) {
            // Http durumunu değiştir
            $response->setStatusCode(201, "Created");

            $log->log_id = $status->getModel()->log_id;

            $response->setJsonContent(
                [
                    "status" => "OK",
                    "data"   => $log
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

            // Hataları döndürmek için
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

// logs id sine bağlı güncelle
$app->put(
    "/api/logs/{id:[0-9]+}",
    function ($id) use ($app) {
        $log = $app->request->getJsonRawBody();

        $db_log = Models\Verilerim\Logs::findFirst("log_id =" . $id);

        $response = new Response();

        if (!$db_kategori) {
             $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "message" => "Belirlenen id de değer yok"
                ]
            );
            return $response;
        }
       
        //id yi değiştirmesini engelle.
        if (isset($log->log_id)) {
            unset($log->log_id);
        }

        foreach ($log as $key => $value) {
            $db_log->$key = $value;
        }

        if ($db_log->save() === false) {

        $messages = $db_log->getMessages();
        $response->setStatusCode(409, "Conflict");
        $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages"   => $messages,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        }

        return $response;
    }
);

// Primary key e göre sil
$app->delete(
    "/api/logs/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "DELETE FROM Models\\Verilerim\\Logs WHERE log_id = :id:";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

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

//*******UserFavlist*********//
// Tüm userfavlisti getir
$app->get(
    "/api/userfavlist",
    function () use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\UserFavlist";
        $userfavlists = $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($userfavlists as $userfavlist) {
            $data[] = [
                "user_favlist_id"   => $userfavlist->user_favlist_id,
                "user_id" => $userfavlist->user_id,
                "urun_id" => $userfavlist->urun_id,
                "kayit_tarihi" => $userfavlist->kayit_tarihi
            ];
        }

        echo json_encode($data);
    }
);

// Ürün adını arama yap
// userfavlistte Arama Yap
$app->get(
    "/api/userfavlist/search/{name}",
    function ($name) use ($app) {

        $phql = "SELECT * FROM Models\\Verilerim\\UserFavlist WHERE log_detail LIKE :name:";

        $userfavlists = $app->modelsManager->executeQuery(
            $phql,
            [
                "name" => "%" . $name . "%"
            ]);

        $data = [];

        foreach ($userfavlists as $userfavlist) {
            $data[] = [
                "user_favlist_id"   => $userfavlist->user_favlist_id,
                "user_id" => $userfavlist->user_id,
                "urun_id" => $userfavlist->urun_id,
                "kayit_tarihi" => $userfavlist->kayit_tarihi
            ];
        }

        echo json_encode($data);

    }
);

// Primary Keye bağlı userfavlisti getir
$app->get(
    "/api/userfavlist/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\UserFavlist WHERE user_favlist_id = :id:";

        $userfavlist = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        )->getFirst();


        $response = new Response();

        if ($userfavlist === false) {
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
                        "user_favlist_id"   => $userfavlist->user_favlist_id,
                        "user_id" => $userfavlist->user_id,
                        "urun_id" => $userfavlist->urun_id,
                        "kayit_tarihi" => $userfavlist->kayit_tarihi
                    ]
                ]
            );
        }

        return $response;
    }
);

// Yeni bir userfavlist ekle
$app->post(
    "/api/userfavlist",
    function () use ($app) {

        $userfavlist = $app->request->getJsonRawBody();

        $phql = "INSERT INTO Models\\Verilerim\\UserFavlist 
        (user_id,urun_id) VALUES 
        (:user_id:,:urun_id:)";


        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "user_id" => $userfavlist->user_id,
                "urun_id" => $userfavlist->urun_id,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        // veri oluşturma başarılımı kontrol et
        if ($status->success() === true) {
            // Http durumunu değiştir
            $response->setStatusCode(201, "Created");

            $userfavlist->user_favlist_id = $status->getModel()->user_favlist_id;

            $response->setJsonContent(
                [
                    "status" => "OK",
                    "data"   => $userfavlist
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

            // Hataları döndürmek için
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

// userfavlist id sine bağlı güncelle
$app->put(
    "/api/userfavlist/{id:[0-9]+}",
    function ($id) use ($app) {
        $userfavlist = $app->request->getJsonRawBody();

        $db_userfavlist = Models\Verilerim\UserFavlist::findFirst("user_favlist_id =" . $id);

        $response = new Response();

        if (!$db_userfavlist) {
             $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "message" => "Belirlenen id de değer yok"
                ]
            );
            return $response;
        }
       
        //id yi değiştirmesini engelle.
        if (isset($userfavlist->user_favlist_id)) {
            unset($userfavlist->user_favlist_id);
        }
//TODO isset ile kontrol et
        foreach ($userfavlist as $key => $value) {
            $db_userfavlist->$key = $value;
        }

        if ($db_userfavlist->save() === false) {

        $messages = $db_userfavlist->getMessages();
        $response->setStatusCode(409, "Conflict");
        $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages"   => $messages,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        }

        return $response;
    }
);

// Primary key e göre sil
$app->delete(
    "/api/userfavlist/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "DELETE FROM Models\\Verilerim\\UserFavlist WHERE user_favlist_id = :id:";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

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

//*******Ürün Yorumları*********//
// Tüm urunyorumlarini getir
$app->get(
    "/api/urunyorumlar",
    function () use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\UrunYorumlar";
        $urunyorumlari = $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($urunyorumlari as $urunyorum) {
            $data[] = [
                "urun_yorumlari_id"   => $urunyorum->urun_yorumlari_id,
                "user_id" => $urunyorum->user_id,
                "urun_id" => $urunyorum->urun_id,
                "puan_hiz" => $urunyorum->puan_hiz,
                "puan_paketleme" => $urunyorum->puan_paketleme,
                "puan_lezzet" => $urunyorum->puan_lezzet,
                "kayit_tarihi" => $urunyorum->kayit_tarihi
            ];
        }

        echo json_encode($data);
    }
);

/*// Ürün yorumu arama yap
// urunyorumlarinda Arama Yap
$app->get(
    "/api/urunyorumlar/search/{name}",
    function ($name) use ($app) {

        $phql = "SELECT * FROM Models\\Verilerim\\UrunYorumlar WHERE log_detail LIKE :name:";

        $urunyorumlari = $app->modelsManager->executeQuery(
            $phql,
            [
                "name" => "%" . $name . "%"
            ]);

        $data = [];

        foreach ($urunyorumlari as $urunyorum) {
            $data[] = [
                "user_favlist_id"   => $urunyorum->user_favlist_id,
                "user_id" => $urunyorum->user_id,
                "urun_id" => $urunyorum->urun_id,
                "kayit_tarihi" => $urunyorum->kayit_tarihi
            ];
        }

        echo json_encode($data);

    }
);*/

// Primary Keye bağlı urunyorumi getir
$app->get(
    "/api/urunyorumlar/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\UrunYorumlar WHERE urun_yorumlari_id = :id:";

        $urunyorum = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        )->getFirst();


        $response = new Response();

        if ($urunyorum === false) {
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
                        "urun_yorumlari_id"   => $urunyorum->urun_yorumlari_id,
                        "user_id" => $urunyorum->user_id,
                        "urun_id" => $urunyorum->urun_id,
                        "puan_hiz" => $urunyorum->puan_hiz,
                        "puan_paketleme" => $urunyorum->puan_paketleme,
                        "puan_lezzet" => $urunyorum->puan_lezzet,
                        "kayit_tarihi" => $urunyorum->kayit_tarihi
                    ]
                ]
            );
        }

        return $response;
    }
);

// Yeni bir urunyorumlari ekle
$app->post(
    "/api/urunyorumlar",
    function () use ($app) {

        $urunyorum = $app->request->getJsonRawBody();

        $phql = "INSERT INTO Models\\Verilerim\\UrunYorumlar 
        (user_id,urun_id,puan_hiz,puan_paketleme,puan_lezzet) VALUES 
        (:user_id:,:urun_id:,:puan_hiz:,:puan_paketleme:,:puan_lezzet:)";


        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "user_id" => $urunyorum->user_id,
                "urun_id" => $urunyorum->urun_id,
                "puan_hiz" => $urunyorum->puan_hiz,
                "puan_paketleme" => $urunyorum->puan_paketleme,
                "puan_lezzet" => $urunyorum->puan_lezzet
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        // veri oluşturma başarılımı kontrol et
        if ($status->success() === true) {
            // Http durumunu değiştir
            $response->setStatusCode(201, "Created");

            $urunyorum->urun_yorumlari_id = $status->getModel()->urun_yorumlari_id;

            $response->setJsonContent(
                [
                    "status" => "OK",
                    "data"   => $urunyorum
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

            // Hataları döndürmek için
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

// urunyorum id sine bağlı güncelle
$app->put(
    "/api/urunyorumlar/{id:[0-9]+}",
    function ($id) use ($app) {
        $urunyorum = $app->request->getJsonRawBody();

        $db_urunyorum = Models\Verilerim\UrunYorumlar::findFirst("urun_yorumlari_id =" . $id);

        $response = new Response();

        if (!$db_urunyorum) {
             $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "message" => "Belirlenen id de değer yok"
                ]
            );
            return $response;
        }
       
        //id yi değiştirmesini engelle.
        if (isset($urunyorum->urun_yorumlari_id)) {
            unset($urunyorum->urun_yorumlari_id);
        }

        foreach ($urunyorum as $key => $value) {
            $db_urunyorum->$key = $value;
        }

        if ($db_urunyorum->save() === false) {

        $messages = $db_urunyorum->getMessages();
        $response->setStatusCode(409, "Conflict");
        $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages"   => $messages,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        }

        return $response;
    }
);

// Primary key e göre sil
$app->delete(
    "/api/urunyorumlar/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "DELETE FROM Models\\Verilerim\\UrunYorumlar WHERE urun_yorumlari_id = :id:";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

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


//*******Siparişler*********//
// Tüm siparislerdni getir
$app->get(
    "/api/siparisler",
    function () use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Siparisler";
        $siparislerd = $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($siparislerd as $siparis) {
            $data[] = [
                "siparis_id"   => $siparis->siparis_id,
                "user_id" => $siparis->user_id,
                "urun_id" => $siparis->urun_id,
                "siparis_tarihi" => $siparis->siparis_tarihi,
                "onay_tarihi" => $siparis->onay_tarihi,
                "aktif" => $siparis->aktif
            ];
        }

        echo json_encode($data);
    }
);

/*// Ürün yorumu arama yap
// siparislerdnda Arama Yap
$app->get(
    "/api/siparisler/search/{name}",
    function ($name) use ($app) {

        $phql = "SELECT * FROM Models\\Verilerim\\Siparisler WHERE log_detail LIKE :name:";

        $siparislerd = $app->modelsManager->executeQuery(
            $phql,
            [
                "name" => "%" . $name . "%"
            ]);

        $data = [];

        foreach ($siparislerd as $siparis) {
            $data[] = [
                "user_favlist_id"   => $siparis->user_favlist_id,
                "user_id" => $siparis->user_id,
                "urun_id" => $siparis->urun_id,
                "kayit_tarihi" => $siparis->kayit_tarihi
            ];
        }

        echo json_encode($data);

    }
);*/

// Primary Keye bağlı siparisi getir
$app->get(
    "/api/siparisler/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "SELECT * FROM Models\\Verilerim\\Siparisler WHERE siparis_id = :id:";

        $siparis = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,
            ]
        )->getFirst();


        $response = new Response();

        if ($siparis === false) {
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
                        "siparis_id"   => $siparis->siparis_id,
                        "user_id" => $siparis->user_id,
                        "urun_id" => $siparis->urun_id,
                        "siparis_tarihi" => $siparis->siparis_tarihi,
                        "onay_tarihi" => $siparis->onay_tarihi,
                        "aktif" => $siparis->aktif
                    ]
                ]
            );
        }

        return $response;
    }
);

//TODO onay tarihi kısmı için özel yapı gerekiyor
// Yeni bir siparislerd ekle
$app->post(
    "/api/siparisler",
    function () use ($app) {

        $siparis = $app->request->getJsonRawBody();

        $phql = "INSERT INTO Models\\Verilerim\\Siparisler 
        (user_id,urun_id,siparis_tarihi,onay_tarihi,aktif) VALUES 
        (:user_id:,:urun_id:,:siparis_tarihi:,:onay_tarihi:,:aktif:)";


        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "user_id" => $siparis->user_id,
                "urun_id" => $siparis->urun_id,
                "siparis_tarihi" => $siparis->siparis_tarihi,
                "onay_tarihi" => $siparis->onay_tarihi,
                "aktif" => $siparis->aktif
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        // veri oluşturma başarılımı kontrol et
        if ($status->success() === true) {
            // Http durumunu değiştir
            $response->setStatusCode(201, "Created");

            $siparis->siparis_id = $status->getModel()->siparis_id;

            $response->setJsonContent(
                [
                    "status" => "OK",
                    "data"   => $siparis
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

            // Hataları döndürmek için
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

// siparis id sine bağlı güncelle
$app->put(
    "/api/siparisler/{id:[0-9]+}",
    function ($id) use ($app) {
        $siparis = $app->request->getJsonRawBody();

        $db_siparis = Models\Verilerim\Siparisler::findFirst("siparis_id =" . $id);

        $response = new Response();

        if (!$db_siparis) {
             $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "message" => "Belirlenen id de değer yok"
                ]
            );
            return $response;
        }
       
        //id yi değiştirmesini engelle.
        if (isset($siparis->siparis_id)) {
            unset($siparis->siparis_id);
        }

        foreach ($siparis as $key => $value) {
            $db_siparis->$key = $value;
        }

        if ($db_siparis->save() === false) {

        $messages = $db_siparis->getMessages();
        $response->setStatusCode(409, "Conflict");
        $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages"   => $messages,
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        }

        return $response;
    }
);

// Primary key e göre sil
$app->delete(
    "/api/siparisler/{id:[0-9]+}",
    function ($id) use ($app) {
        $phql = "DELETE FROM Models\\Verilerim\\Siparisler WHERE siparis_id = :id:";

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id
            ]
        );

        // Yanıt Oluştur
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
        } else {
            // Http durumunu değiştir
            $response->setStatusCode(409, "Conflict");

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


$app->handle();