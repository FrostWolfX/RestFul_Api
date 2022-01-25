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
                    match (is_numeric($value)) {
                        true => $this->$name = (int)$value,
                        false => throw new \Exception("Property '{$name}' don't have a right type data")
                    };
                    break;
                default:
                    match ($rp->getType()->getName() === get_debug_type($value)) {
                        true => $this->$name = $value,
                        false => throw new \Exception("Property '{$name}' don't have a right type data")
                    };
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
        $productsItem = [];
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
                $productsArr[] = $productsItem;
            }
        }

        return $productsArr;

    }

    public function product(): array
    {
        $productsArr = [];
        $productsItem = [];
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
        $request->bindParam(1, $this->id);
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
                $productsArr = $productsItem;
            }
        }
        return $productsArr;
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

    public function delete(): bool
    {
        $query = "
            DELETE FROM {$this->tableName} WHERE id = :id
        ";

        $query = $this->connect->prepare($query);
        //clear
        $this->id = htmlspecialchars(strip_tags($this->id));
        //bind value
        $query->bindParam(':id', $this->id);

        //start query DB
        if ($query->execute()) {
            return true;
        }
        return false;
    }
}