<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Metrics;
use Prometheus\RenderTextFormat;

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER["REQUEST_METHOD"];

// Rota para expor as métricas para o Prometheus
if ($uri == '/metrics' && $requestMethod == 'GET') {
    $renderer = new RenderTextFormat();
    header('Content-type: ' . RenderTextFormat::MIME_TYPE);
    echo $renderer->render(Metrics::getRegistry()->getMetricFamilySamples());
    exit();
}

// Roteamento principal com instrumentação
if ($uri == '/users' && $requestMethod == 'GET') {
    Metrics::incrementRequestCounter('list_users');
    $controller = new App\UserController();
    $controller->listUsers();
} elseif ($uri == '/users' && $requestMethod == 'POST') {
    Metrics::incrementRequestCounter('create_user');
    $controller = new App\UserController();
    $controller->createUser();
} else {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(['error' => 'Endpoint not found']);
    exit();
}
?>
