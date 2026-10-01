<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$dataFile = __DIR__ . '/confirmaciones.json';

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, "[]");
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_payload']);
    exit;
}

$code = strtoupper(trim($input['code'] ?? ''));

$record = [
    'created_at' => date('c'),
    'code' => $code,
    'invitation_name' => trim($input['invitation_name'] ?? ''),
    'table_number' => trim($input['table_number'] ?? ''),
    'name' => trim($input['name'] ?? ''),
    'attendance' => trim($input['attendance'] ?? ''),
    'adults' => max(0, (int)($input['adults'] ?? 0)),
    'children' => max(0, (int)($input['children'] ?? 0)),
    'message' => trim($input['message'] ?? ''),
];

$handle = fopen($dataFile, 'c+');

if (!$handle) {
    http_response_code(500);
    echo json_encode(['error' => 'storage_unavailable']);
    exit;
}

flock($handle, LOCK_EX);

$contents = stream_get_contents($handle);
$confirmations = json_decode($contents ?: '[]', true);

if (!is_array($confirmations)) {
    $confirmations = [];
}

if ($code !== '') {
    foreach ($confirmations as $confirmation) {
        if (strtoupper($confirmation['code'] ?? '') === $code) {
            flock($handle, LOCK_UN);
            fclose($handle);
            http_response_code(409);
            echo json_encode(['error' => 'already_confirmed']);
            exit;
        }
    }
}

$confirmations[] = $record;

ftruncate($handle, 0);
rewind($handle);
fwrite($handle, json_encode($confirmations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
fflush($handle);
flock($handle, LOCK_UN);
fclose($handle);

echo json_encode(['ok' => true]);
