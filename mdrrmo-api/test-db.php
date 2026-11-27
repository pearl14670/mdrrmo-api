<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../src/db_connection.php';

try {
    $pdo = getDatabaseConnection();
    $version = $pdo->query('SELECT version()')->fetchColumn();

    echo json_encode([
        'status'  => 'success',
        'message' => 'Database connected successfully!',
        'version' => $version
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
