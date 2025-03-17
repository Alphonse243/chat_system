<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    session_start();
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON');
    }

    if (!isset($data['lang'])) {
        throw new Exception('Language not specified');
    }

    $allowedLangs = ['fr', 'en', 'es', 'zh', 'sw'];
    if (!in_array($data['lang'], $allowedLangs)) {
        throw new Exception('Invalid language');
    }

    $_SESSION['lang'] = $data['lang'];
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
