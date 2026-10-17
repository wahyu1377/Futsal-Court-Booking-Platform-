<?php
function respond(bool $ok, string $message, array $extra = [], int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $ok, 'message' => $message], $extra));
    exit;
}