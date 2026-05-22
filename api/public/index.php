<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/src/Http/Router.php';

$router = new Router();
require dirname(__DIR__) . '/routes/api.v1.php';

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$path = parse_url($uri, PHP_URL_PATH);

// Normalize path: strip deployment prefix for both local and production.
// Local:      /ru-api/php/public/api/v1/...   → /api/v1/...
// Production: /php/public/api/v1/...           → /api/v1/...
$path = preg_replace('#^(?:/api/anchor)?/public#', '', $path);
if ($path === '') {
    $path = '/';
}

$request = request_data();
$response = $router->dispatch($method, $path, $request);

$statusCode = isset($response['code']) ? (int) $response['code'] : 200;
http_response_code($statusCode);

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
