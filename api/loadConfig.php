<?php
// Carrega as configurações de produção ou desenvolvimento
$envPath = __DIR__ . "/";
$envFile = '.env';

if (file_exists($envPath . '.env.development')) {
    define("DEV_MODE", true);
    $envFile = '.env.development';
} elseif (file_exists($envPath . '.env')) {
    define("DEV_MODE", false);
    $envFile = '.env';
}

$dotenv = Dotenv\Dotenv::createImmutable($envPath, $envFile);
$dotenv->load();

// Carrega as configurações de CORS
$allowed_origin = $_ENV['ALLOWED_ORIGIN'];

if (DEV_MODE) {
    header("Access-Control-Allow-Origin: $allowed_origin");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, userId, token");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header("HTTP/1.1 200 OK");
        exit;
    }
}
