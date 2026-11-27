<?php
header('Content-Type: application/json');

echo json_encode([
    'success'   => true,
    'message'   => 'Mahaplag MDRRMO API is running.',
    'endpoints' => [
        'POST /api/incidents/create.php',
        'GET /api/incidents/list.php',
        'GET /api/incidents/view.php?id=1'
    ]
]);
