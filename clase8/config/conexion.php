<?php
// PDO + MySQL, compatible con la base ya creada en el TP6.
require_once __DIR__ . '/env.php';

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$base_datos = $_ENV['DB_NAME'] ?? 'padel_tournament_organizer';
$usuario_bd = $_ENV['DB_USER'] ?? 'root';
$clave_bd = $_ENV['DB_PASS'] ?? '';
$puerto_bd = $_ENV['DB_PORT'] ?? '3306';

$dsn = "mysql:host={$host};port={$puerto_bd};dbname={$base_datos};charset=utf8mb4";
$opciones = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $usuario_bd, $clave_bd, $opciones);
} catch (PDOException $e) {
    error_log('Error PDO de conexión: ' . $e->getMessage());
    http_response_code(503);
    exit('No se pudo conectar a la base de datos. Comprobá MySQL en XAMPP y el archivo .env.');
}
