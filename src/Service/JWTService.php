<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

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

    /**
     * Alias for isTokenValid (backward compatibility)
     */
    public function isValide(string $token): bool
    {
        return $this->isTokenValid($token);
    }

    /**
     * Check if token is expired
     */
    public function isExpired(string $token): bool
    {
        try {
            JWT::decode($token, new Key($this->secret, 'HS256'));
            return false;
        } catch (ExpiredException $e) {
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verify token signature
     */
    public function check(string $token, string $secret): bool
    {
        try {
            JWT::decode($token, new Key($secret, 'HS256'));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get payload from token
     */
    public function getPayload(string $token): ?array
    {
        return $this->decodeToken($token);
    }

    /**
     * Generate token with header, payload and secret (backward compatibility)
     */
    public function generate(array $header, array $payload, string $secret): string
    {
        // Store original secret temporarily to use new one
        $originalSecret = $this->secret;
        $this->secret = $secret;
        
        try {
            $token = $this->createToken($payload);
        } finally {
            $this->secret = $originalSecret;
        }

        return $token;
    }
}
