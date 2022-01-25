<?php

use api\config\Database;

require_once __DIR__ . '/../../Autoload.php';
Autoload::register();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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

function getProduct(int $id)
{
    header("Content-Type: application/json; charset=utf8");
    $database = new Database();
    try {
        $dbConnect = $database->getConnection();
        //Проверка, что объект является PDO
        if ($dbConnect instanceof PDO) {
            $productsObj = new \api\products\Products($dbConnect);
            $productsObj->id = $id;
            //запрашиваем данные
            $product = $productsObj->product();
            if ($product) {
                http_response_code(200);
                echo json_encode(['status' => true, 'message' => $product]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => false, 'message' => "Товар id = {$productsObj->id} не найден"]);
            }
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

function updateProduct(int $id)
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

function deleteProduct(int $id)
{
    $database = new Database();
    try {
        $dbConnect = $database->getConnection();
        if ($dbConnect instanceof PDO) {
            $product = new \api\products\Products($dbConnect);
            $product->id = $id;

            if($product->delete()) {
                http_response_code(200);
                echo json_encode(['status' => true, 'message' => 'Товар был удален'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(503);
                echo json_encode(['status' => false, 'message' => 'Не удалось удалить товар.']);
            }
        }
    }catch (Exception $exception) {
        http_response_code(404);
        echo json_encode(['status' => false, 'message' => $exception->getMessage()]);
    }
}