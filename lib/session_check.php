<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/solicitud_registro_usuario/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_ACTIVE) {
    echo json_encode(['success' => true, 'session_id' => session_id()]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to initialize session']);
}