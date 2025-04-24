<?php
// Carga Composer + dotenv
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Obtener clave de NewsAPI
$newsApiKey = $_ENV['NEWSAPI_KEY'] ?? '';
if (!$newsApiKey) {
    throw new Exception("No se encontró NEWSAPI_KEY en el archivo .env");
}
