<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    private string $secret;
    private int $tokenTTL;

    public function __construct(string $jwtSecret, int $jwtTTL = 3600)
    {
        $this->secret = $jwtSecret;
        $this->tokenTTL = $jwtTTL;
    }

    public function createToken(array $payload): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->tokenTTL;

        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expire,
        ]);

        return JWT::encode($tokenPayload, $this->secret, 'HS256');
    }

    public function decodeToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isTokenValid(string $token): bool
    {
        return $this->decodeToken($token) !== null;
    }
}
