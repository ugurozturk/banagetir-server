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

// usergroups Adresinda Arama Yap
$app->get(
    "/api/usergroups/search/{name}",
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


// Primary Keye bağlı ürünü getir
$app->get(
    "/api/usergroups/{id:[0-9]+}",
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



$app->handle();