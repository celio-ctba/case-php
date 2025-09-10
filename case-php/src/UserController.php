<?php

namespace App;

class UserController {

    private $db;
    private $user;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->user = new User($this->db);
    }

    public function listUsers()
    {
        $result = $this->user->findAll();
        header("HTTP/1.1 200 OK");
        echo json_encode($result);
    }

    public function createUser()
    {
        $input = (array) json_decode(file_get_contents('php://input'), true);

        if (! $this->validateUserInput($input)) {
            header("HTTP/1.1 422 Unprocessable Entity");
            echo json_encode(['error' => 'Invalid input']);
            return;
        }

        $this->user->create($input);
        header("HTTP/1.1 201 Created");
        echo json_encode(['message' => 'User created successfully']);
    }

    private function validateUserInput(array $input)
    {
        if (! isset($input['name']) || empty($input['name'])) {
            return false;
        }
        if (! isset($input['email']) || ! filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        if (! isset($input['password']) || strlen($input['password']) < 6) {
            return false;
        }
        return true;
    }
}
?>