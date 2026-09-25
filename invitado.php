<?php
header('Content-Type: application/json; charset=utf-8');

$dataFile = __DIR__ . '/invitados.json';
$code = strtoupper(trim($_GET['code'] ?? ''));

if ($code === '' || !file_exists($dataFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}

$guests = json_decode(file_get_contents($dataFile), true);

if (!is_array($guests)) {
    http_response_code(500);
    echo json_encode(['error' => 'invalid_data']);
    exit;
}

foreach ($guests as $guest) {
    if (strtoupper($guest['code'] ?? '') === $code) {
        echo json_encode([
            'code' => $guest['code'],
            'name' => $guest['name'],
            'adults' => (int)$guest['adults'],
            'children' => (int)$guest['children'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

http_response_code(404);
echo json_encode(['error' => 'not_found']);
