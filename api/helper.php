<?php
if (!function_exists('json_error')) {
    function json_error($message, $code = 500) {
        http_response_code($code);
        echo json_encode(['error' => $message]);
        exit;
    }
}
