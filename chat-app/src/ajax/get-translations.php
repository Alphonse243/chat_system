<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    if (!isset($_GET['lang'])) {
        throw new Exception('Language parameter is required');
    }

    $lang = $_GET['lang'];
    $file = __DIR__ . "/../translations/{$lang}.php";

    if (!file_exists($file)) {
        throw new Exception("Translation file not found for: {$lang}");
    }

    $translations = require $file;

    echo json_encode([
        'success' => true,
        'data' => $translations
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
