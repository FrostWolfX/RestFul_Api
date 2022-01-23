<?php

use api\config\Database;

require_once __DIR__ . '/../../Autoload.php';

Autoload::register();

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
            echo json_encode(['status' => true, 'body' => $products]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => false, 'body' => 'Не удалось соединиться к DB']);
        }
    } catch (\api\exception\DbException|Exception $e) {
        http_response_code(404);
        echo json_encode(['status' => false, 'body' => $e->getMessage()]);
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
            echo json_encode(['status' => false, 'body' => 'Не удалось соединиться к DB']);
        }
    } catch (\api\exception\DbException|Exception $e) {
        http_response_code(404);
        echo json_encode(['status' => false, 'body' => $e->getMessage()]);
    }
}

function addNewProduct() {
    $post = $_POST ?? [];

    $name = isset($post['name']) ? $post['name'] : '';
    $description = isset($post['description']) ? $post['description'] : '';
    $price = isset($post['price']) ? $post['price'] : 0;
    $categoryId = isset($post['categoryId']) ? $post['categoryId'] : 0;

    header("Content-type: application/json; charset=utf8");
    $database = new Database();
    try {
        $dbConnection = $database->getConnection();
        if ($dbConnection instanceof PDO) {
            $productObj = new \api\products\Products($dbConnection);
            // устанавливаем значения свойств товара
            $productObj->name = $name;
            $productObj->description = $description;
            $productObj->price = $price;
            $productObj->categoryId = $categoryId;

            $productObj->add();
        } else {
            http_response_code(404);
            echo json_encode(['status' => false, 'body' => 'Не удалось соединиться к DB']);
        }
    } catch (\api\exception\DbException|Exception $e) {
        http_response_code(404);
        echo json_encode(['status' => false, 'body' => $e->getMessage()]);
    }
}