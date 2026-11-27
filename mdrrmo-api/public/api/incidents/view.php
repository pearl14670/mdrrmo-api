<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function jsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET, OPTIONS');
    jsonResponse(405, [
        'success' => false,
        'message' => 'Method not allowed. Use GET to retrieve an incident.'
    ]);
}

require_once __DIR__ . '/../../../src/db_connection.php';

try {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id < 1) {
        jsonResponse(400, [
            'success' => false,
            'message' => 'A valid incident ID is required.'
        ]);
    }

    $pdo = getDatabaseConnection();

    $sql = '
        SELECT
            incident_id,
            emergency_type,
            description,
            persons_affected,
            latitude,
            longitude,
            gps_accuracy,
            status,
            reported_at,
            updated_at
        FROM public.emergency_incidents
        WHERE incident_id = :id
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    $incident = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$incident) {
        jsonResponse(404, [
            'success' => false,
            'message' => 'Incident not found.'
        ]);
    }

    $incident['incident_id'] = (int) $incident['incident_id'];

    foreach (['persons_affected', 'latitude', 'longitude', 'gps_accuracy'] as $field) {
        if ($incident[$field] !== null && is_numeric($incident[$field])) {
            $incident[$field] = (float) $incident[$field];
        }
    }

    jsonResponse(200, [
        'success' => true,
        'data' => $incident
    ]);

} catch (Throwable $e) {
    error_log('MDRRMO incident view API error: ' . $e->getMessage());

    jsonResponse(500, [
        'success' => false,
        'message' => 'Unable to retrieve the incident. Check the Render server logs.'
    ]);
}
