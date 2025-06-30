<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generateJWT($user, $secret) {
    $issuer = $_ENV['APP_URL'] ?? 'http://localhost';

    $payload = [
        'iss' => $issuer,
        'aud' => $issuer,
        'iat' => time(),
        'exp' => time() + 3600,
        'data' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'role' => $user['role']
        ]
    ];

    return JWT::encode($payload, $secret, 'HS256');
}
