<?php

use api\config\Database;

require_once __DIR__ . '/../../Autoload.php';
Autoload::register();

header("Content-Type: application/json; charset=utf8");

function getProducts()
{
    header("Content-Type: application/json; charset=utf8");
    $database = new Database();
    try {
        $dbConnect = $database->getConnection();
        //Проверка, что объект является PDO
        if ($dbConnect instanceof PDO) {
            $productsObj = new \api\products\Products($dbConnect);
            //запрашиваем данные
            $products = $productsObj->products();
            //устанавливаем код ответа - 200 ОК
            http_response_code(200);
            echo json_encode(['status' => true, 'message' => $products]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => false, 'message' => 'Не удалось соединиться к DB']);
        }
    } catch (\api\exception\DbException|Exception $e) {
        http_response_code(404);
        echo json_encode(['status' => false, 'message' => $e->getMessage()]);
    }
}

function getProduct($id)
{
    header("Content-Type: application/json; charset=utf8");
    $database = new Database();
    try {
        $dbConnect = $database->getConnection();
        //Проверка, что объект является PDO
        if ($dbConnect instanceof PDO) {
            $productsObj = new \api\products\Products($dbConnect);
            //запрашиваем данные
            $productsObj->product($id);
        } else {
            http_response_code(404);
            echo json_encode(['status' => false, 'message' => 'Не удалось соединиться к DB']);
        }
    } catch (\api\exception\DbException|Exception $e) {
        http_response_code(404);
        echo json_encode(['status' => false, 'message' => $e->getMessage()]);
    }
}

function createProduct()
{
    $post = $_POST ?? [];

    try {
        // убеждаемся, что данные не пусты
        if (
            !empty($post['name']) &&
            !empty($post['description']) &&
            !empty($post['price']) &&
            !empty($post['category_id'])
        ) {
            $database = new Database();
            $dbConnect = $database->getConnection();
            if ($dbConnect instanceof PDO) {
                $productObj = new \api\products\Products($dbConnect);

                // установим значения свойств товара
                foreach ($post as $columnName => $value) {
                    $productObj->$columnName = $value;
                }
                $id = $productObj->createProduct();
                if ($id) {
                    http_response_code(201);
                    echo json_encode(['status' => true, 'message' => $id], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => false, 'message' => 'Ошибка добавления товара']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['status' => false, 'message' => 'Не удалось соединиться к DB']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['status' => false, 'message' => 'Не все данные заполнены']);
        }
    } catch (\api\exception\DbException|Exception $e) {
        http_response_code(404);
        echo json_encode(['status' => false, 'message' => $e->getMessage()]);
    }
}

function update(int $id)
{
    $database = new Database();
    try {
        $dbConnect = $database->getConnection();
        if ($dbConnect instanceof PDO) {

            $data = json_decode(file_get_contents("php://input"), true);

            $productObj = new \api\products\Products($dbConnect);
            // установим значения свойств товара
            foreach ($data as $columnName => $value) {
                $productObj->$columnName = $value;
            }
            $productObj->id = $id;

            if ($productObj->update()) {
                http_response_code(200);
                echo json_encode(['status' => true, 'message' => 'Товар обновлен'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(503);
                echo json_encode(['status' => false, 'message' => 'Невозможно обновить товар.']);
            }
        }
    } catch (Exception $exception) {
        http_response_code(404);
        echo json_encode(['status' => false, 'message' => $exception->getMessage()]);
    }
}