<?php

// Development error visibility (localhost)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Basic JSON API defaults
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

spl_autoload_register(function ($className) {
    $baseDir = __DIR__ . '/src';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));

    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }

        if ($file->getExtension() !== 'php') {
            continue;
        }

        if ($file->getBasename('.php') === $className) {
            require_once $file->getPathname();
            return;
        }
    }
});

function request_json_body()
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return array();
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : array();
}

function request_data()
{
    $data = request_json_body();

    if (!empty($_GET)) {
        $data = array_merge($data, $_GET);
    }

    if (!empty($_POST)) {
        $data = array_merge($data, $_POST);
    }

    return $data;
}
