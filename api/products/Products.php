<?php

namespace api\products;

use http\Encoding\Stream\Inflate;
use ReflectionProperty;

/**
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $price
 * @property integer $category_id
 */
class Products
{
    private \PDO $connect;
    private string $tableName = 'products';
    protected int $id;
    protected string $name;
    protected string $description;
    protected int $price;
    protected int $category_id;
    protected string $modified;

    public function __construct(\PDO $db)
    {
        $this->connect = $db;
    }

    /**
     * @throws \Exception
     */
    public function __set(string $name, $value)
    {
        $rp = new ReflectionProperty($this, $name);
        if (property_exists($this, $name)) {
            switch ($rp->getType()->getName()):
                case 'int':
                    if (is_numeric($value)) {
                        $this->$name = (int)$value;
                    } else {
                        throw new \Exception("Property '{$name}' don't have a right type data");
                    }
                    break;
                default:
                    if ($rp->getType()->getName() === get_debug_type($value)) {
                        $this->$name = $value;
                    } else {
                        throw new \Exception("Property '{$name}' don't have a right type data");
                    }
            endswitch;
        } else {
            throw new \Exception("Property '{$name}' not found");
        }
    }

    /**
     * @throws \Exception
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
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
                    echo json_encode(['status' => false, 'body' => "Товар id = {$id} не найден"]);
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

    /**
     * Добавление товара
     * @return int
     */
    public function createProduct(): int
    {
        $query = "
                INSERT INTO {$this->tableName}
                    (name, description, price, category_id)
                VALUES 
                    (:name, :description, :price, :category_id)
            ";

        // подготовка запроса
        $request = $this->connect->prepare($query);
        // очистка
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        // привязка значений
        $request->bindParam(":name", $this->name);
        $request->bindParam(":description", $this->description);
        $request->bindParam(":price", $this->price);
        $request->bindParam(":category_id", $this->category_id);

        // выполняем запрос
        if ($request->execute()) {
            return $this->connect->lastInsertId();
        }
        return 0;
    }

    /**
     * Обновление товара
     * @return bool
     */
    public function update(): bool
    {
        $query = "
            UPDATE
                {$this->tableName}
            SET 
                name = :name,
                price = :price,
                description = :description,
                category_id = :category_id,
                modified = :modified
            WHERE
                id = :id
        ";

        $query = $this->connect->prepare($query);

        //clear
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->modified = date('Y-m-d H:i:s');

        //bind properties
        $query->bindParam(':name', $this->name);
        $query->bindParam(':price', $this->price);
        $query->bindParam(':description', $this->description);
        $query->bindParam(':category_id', $this->category_id);
        $query->bindParam(':id', $this->id);
        $query->bindParam(':modified', $this->modified);

        //start query execute
        if ($query->execute()) {
            return true;
        }

        return false;
    }
}