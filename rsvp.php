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

$confirmations = json_decode(file_get_contents($dataFile), true);

if (!is_array($confirmations)) {
    $confirmations = [];
}

$record = [
    'created_at' => date('c'),
    'code' => strtoupper(trim($input['code'] ?? '')),
    'invitation_name' => trim($input['invitation_name'] ?? ''),
    'name' => trim($input['name'] ?? ''),
    'attendance' => trim($input['attendance'] ?? ''),
    'adults' => max(0, (int)($input['adults'] ?? 0)),
    'children' => max(0, (int)($input['children'] ?? 0)),
    'message' => trim($input['message'] ?? ''),
];

$confirmations[] = $record;

file_put_contents($dataFile, json_encode($confirmations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['ok' => true]);
