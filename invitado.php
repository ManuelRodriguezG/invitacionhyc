<?php
header('Content-Type: application/json; charset=utf-8');

$dataFile = __DIR__ . '/invitados.json';
$confirmationsFile = __DIR__ . '/confirmaciones.json';
$code = strtoupper(trim($_GET['code'] ?? ''));

if ($code === '' || !file_exists($dataFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}

$guests = json_decode(file_get_contents($dataFile), true);
$confirmations = file_exists($confirmationsFile) ? json_decode(file_get_contents($confirmationsFile), true) : [];

if (!is_array($guests)) {
    http_response_code(500);
    echo json_encode(['error' => 'invalid_data']);
    exit;
}

if (!is_array($confirmations)) {
    $confirmations = [];
}

$alreadyConfirmed = false;

foreach ($confirmations as $confirmation) {
    if (strtoupper($confirmation['code'] ?? '') === $code) {
        $alreadyConfirmed = true;
        break;
    }
}

foreach ($guests as $guest) {
    if (strtoupper($guest['code'] ?? '') === $code) {
        echo json_encode([
            'code' => $guest['code'],
            'name' => $guest['name'],
            'adults' => (int)$guest['adults'],
            'children' => (int)$guest['children'],
            'table_number' => $guest['table_number'] ?? '',
            'confirmed' => $alreadyConfirmed,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

http_response_code(404);
echo json_encode(['error' => 'not_found']);
