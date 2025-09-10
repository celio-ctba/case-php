<?php

namespace App;

class User {

    private $db;
    private $table = 'users';

    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    public function findAll()
    {
        $statement = "SELECT id, name, email, created_at FROM " . $this->table;
        $result = $this->db->query($statement);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function create(array $input)
    {
        $statement = "
            INSERT INTO " . $this->table . "
                (name, email, password)
            VALUES
                (?, ?, ?);
        ";

        $stmt = $this->db->prepare($statement);
        $stmt->bind_param(
            'sss',
            $input['name'],
            $input['email'],
            password_hash($input['password'], PASSWORD_DEFAULT)
        );
        #$stmt->execute();
        #return $stmt->affected_rows;
        return $stmt->execute(); // Retorna true ou false
    }
}
?>