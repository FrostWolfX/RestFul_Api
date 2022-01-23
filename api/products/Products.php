<?php

namespace api\products;

class Products
{
    private \PDO $connect;
    private string $tableName = 'products';

    public function __construct(\PDO $db)
    {
        $this->connect = $db;
    }

    public function __set(string $name, $value): void
    {
        $this->$name = $value;
    }

    /**
     * @throws \Exception
     */
    public function __get(string $name)
    {
        if (!empty($this->$name)) {
            return $this->$name;
        } else {
            throw new \Exception("Ошибка свойство {$name} не найдено");
        }
    }

    /**
     * @throws \Exception
     */
    public function products(): array
    {
        $productsArr = [];
        $productsArr['records'] = [];
        try {
            //получение списка
            $query = "
                SELECT
                    p.id, p.name, p.description, p.price, p.category_id, p.created, p.modified
                FROM {$this->tableName} AS p
                ORDER BY p.id DESC
                LIMIT 30
            ";
            //подготовка запроса
            $request = $this->connect->prepare($query);
            //выполнение запроса
            $request->execute();

            $num = $request->rowCount();
            //проверка найдено ли больше 0 записей
            if ($num > 0) {
                //получение содержимое таблицы
                // fetch() быстрее, чем fetchAll()
                while ($row = $request->fetch(\PDO::FETCH_ASSOC)) {
                    foreach ($row as $columnName => $value) {
                        $productsItem[$columnName] = $value;
                    }
                    $productsArr['records'][] = $productsItem;
                }
            }

            return $productsArr;

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    public function product($id = 0)
    {
        $productsArr = [];
        $productsArr['records'] = [];
        $id = isset($id) ? (int)($id ?? 0) : 0;
        try {
            //получение товара по id
            if ($id) {
                // запрос для чтения одной записи (товара)
                $query = "
                    SELECT
                        p.id, p.name, p.description, p.price, p.category_id, p.created, p.modified
                    FROM {$this->tableName} AS p
                    WHERE p.id = ?
                    LIMIT 1
                ";

                //подготовка запроса
                $request = $this->connect->prepare($query);
                // привязываем id callCenter, который будет обновлен
                $request->bindParam(1, $id);
                //выполнение запроса
                $request->execute();

                $num = $request->rowCount();

                //проверка найдено ли больше 0 записей
                if ($num > 0) {
                    //получение содержимое таблицы
                    while ($row = $request->fetch(\PDO::FETCH_ASSOC)) {
                        foreach ($row as $columnName => $value) {
                            $productsItem[$columnName] = $value;
                        }
                        $productsArr['records'][] = $productsItem;
                    }
                    //устанавливаем код ответа - 200 ОК
                    http_response_code(200);
                    echo json_encode(['status' => true, 'body' => $productsArr]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => false, 'body' => "Товар id = {$id} не найдена"]);
                }

            } else {
                http_response_code(404);
                echo json_encode(['status' => false, 'body' => "Товара с id = {$id} не существует"]);
            }
        } catch (\Exception $exception) {
            http_response_code(404);
            echo json_encode(['status' => false, 'body' => (throw new \Exception($exception->getMessage()))]);
        }
    }

    public function add()
    {
        try {
            $query = "
                INSERT INTO {$this->tableName}
                    (name, description, price, category_id)
                VALUES 
                    (:name, :description, :price, :categoryId)
            ";

            // подготовка запроса
            $request = $this->connect->prepare($query);
            // очистка
            $this->name=htmlspecialchars(strip_tags($this->name));
            $this->description=htmlspecialchars(strip_tags($this->description));
            $this->price=htmlspecialchars(strip_tags($this->price));
            $this->categoryId=htmlspecialchars(strip_tags($this->categoryId));
            // привязка значений
            $request->bindParam(":name", $this->name);
            $request->bindParam(":description", $this->description);
            $request->bindParam(":price", $this->price);
            $request->bindParam(":categoryId", $this->categoryId);

            // выполняем запрос
            if ($request->execute()) {
                $lastId = $this->connect->lastInsertId() - 1;
                http_response_code(201);
                echo json_encode(['status' => true, 'id' => $lastId]);
            }

        } catch (\Exception $exception) {
            http_response_code(404);
            echo json_encode(['status' => false, 'body' => (throw new \Exception($exception->getMessage()))]);
        }
    }
}