<?php

require __DIR__ . '/../vendor/autoload.php';

header("Content-Type: application/json; charset=UTF-8");

// Allow cross-origin requests for development
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER["REQUEST_METHOD"];

// Simple routing
if ($uri == '/users' && $requestMethod == 'GET') {
    $controller = new App\UserController();
    $controller->listUsers();
} elseif ($uri == '/users' && $requestMethod == 'POST') {
    $controller = new App\UserController();
    $controller->createUser();
} else {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(['error' => 'Endpoint not found']);
    exit();
}
