<?php
// api/init.php
require_once __DIR__ . '/../db.php';

if (!function_exists('json_error')) {
    function json_error($message, $code = 500) {
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
}

if (!function_exists('json_success')) {
    function json_success($data = []) {
        http_response_code(200);
        echo json_encode(array_merge(['status' => 'success'], $data));
        exit;
    }
}
