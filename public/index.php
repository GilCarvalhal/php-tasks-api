<?php

/**
 * Health check temporário para validar o roteamento (Apache + .htaccess + Docker).
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if($uri === '/health' || $uri === '/')
{
    header('Content-type: application/json; charset=utf-8');

    http_response_code(200);

    echo json_encode(['status' => 'ok']);

    exit;
}