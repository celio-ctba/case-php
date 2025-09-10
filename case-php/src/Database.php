<?php

namespace App;

class Database {
    private $connection = null;

    public function __construct()
    {
        // Use variáveis de ambiente ou valores padrão para facilitar execução local
        $host = getenv('DB_HOST') ?: '127.0.0.1'; // ou 'localhost'
        $db   = getenv('DB_DATABASE') ?: 'app_db';
        $user = getenv('DB_USER') ?: 'admin';
        $pass = getenv('DB_PASSWORD') ?: 'banco';

        try {
            $this->connection = new \mysqli($host, $user, $pass, $db);
        } catch (\Exception $e) {
            exit('Database connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
//Adicionei uma pequena modificação no `Database.php` para que ele use valores padrão caso as variáveis de ambiente não existam, facilitando a 
//execução local.
?>