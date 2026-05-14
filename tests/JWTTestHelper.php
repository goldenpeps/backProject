<?php

namespace App\Tests;

use Firebase\JWT\JWT;

class JWTTestHelper
{
    private string $secret;

    public function __construct(string $jwtSecret = 'test_secret')
    {
        $this->secret = $jwtSecret;
    }

    public function createValidToken(array $payload = []): string
    {
        $issuedAt = time();
        $expire = $issuedAt + 3600;

        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expire,
            'email' => $payload['email'] ?? 'test@example.com',
        ]);

        return JWT::encode($tokenPayload, $this->secret, 'HS256');
    }
}
