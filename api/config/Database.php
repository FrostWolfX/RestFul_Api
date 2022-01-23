<?php

namespace api\config;

require_once __DIR__ . '/config.php';

class Database
{
    private string $host = HOST;
    private string $db = DB_NAME;
    private string $user = USER_NAME;
    private string $password = PASSWORD;
    private int $port = PORT;
    private \PDO $connection;

    /**
     * @throws \api\exception\DbException
     */
    public function getConnection(): \PDO
    {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db}";
            $this->connection = new \PDO(
                $dsn, $this->user, $this->password
            );
            return $this->connection;
        } catch (\PDOException $exception) {
            throw new \api\exception\DbException('Ошибка при подключении к БД ' . $exception->getMessage());
        }
    }
}