<?php

namespace App\Tests\Service;

use App\Service\JWTService;
use PHPUnit\Framework\TestCase;

class JWTServiceTest extends TestCase
{
    private JWTService $jwtService;
    private string $secret = 'test_secret_key_for_unit_tests_1234567890';

    protected function setUp(): void
    {
        $this->jwtService = new JWTService($this->secret, 3600);
    }

    public function testCreateTokenReturnsString(): void
    {
        $token = $this->jwtService->createToken(['email' => 'test@example.com']);
        self::assertIsString($token);
        self::assertNotEmpty($token);
    }

    public function testCreateTokenHasThreeParts(): void
    {
        $token = $this->jwtService->createToken(['email' => 'test@example.com']);
        $parts = explode('.', $token);
        self::assertCount(3, $parts);
    }

    public function testDecodeTokenReturnsPayload(): void
    {
        $token = $this->jwtService->createToken(['email' => 'decode@example.com']);
        $payload = $this->jwtService->decodeToken($token);

        self::assertIsArray($payload);
        self::assertSame('decode@example.com', $payload['email']);
    }

    public function testDecodeTokenAddsIatAndExp(): void
    {
        $token = $this->jwtService->createToken(['email' => 'exp@example.com']);
        $payload = $this->jwtService->decodeToken($token);

        self::assertArrayHasKey('iat', $payload);
        self::assertArrayHasKey('exp', $payload);
        self::assertGreaterThan($payload['iat'], $payload['exp']);
    }

    public function testDecodeInvalidTokenReturnsNull(): void
    {
        $result = $this->jwtService->decodeToken('invalid.token.here');
        self::assertNull($result);
    }

    public function testDecodeEmptyTokenReturnsNull(): void
    {
        $result = $this->jwtService->decodeToken('');
        self::assertNull($result);
    }

    public function testIsTokenValidWithValidToken(): void
    {
        $token = $this->jwtService->createToken(['email' => 'valid@example.com']);
        self::assertTrue($this->jwtService->isTokenValid($token));
    }

    public function testIsTokenValidWithInvalidToken(): void
    {
        self::assertFalse($this->jwtService->isTokenValid('garbage.token.value'));
    }

    public function testIsValideAliasWorks(): void
    {
        $token = $this->jwtService->createToken(['email' => 'alias@example.com']);
        self::assertTrue($this->jwtService->isValide($token));
        self::assertFalse($this->jwtService->isValide('bad.token'));
    }

    public function testIsExpiredReturnsFalseForValidToken(): void
    {
        $token = $this->jwtService->createToken(['email' => 'fresh@example.com']);
        self::assertFalse($this->jwtService->isExpired($token));
    }

    public function testIsExpiredReturnsFalseForInvalidToken(): void
    {
        self::assertFalse($this->jwtService->isExpired('not.a.valid.jwt'));
    }

    public function testCheckWithCorrectSecret(): void
    {
        $token = $this->jwtService->createToken(['email' => 'check@example.com']);
        self::assertTrue($this->jwtService->check($token, $this->secret));
    }

    public function testCheckWithWrongSecret(): void
    {
        $token = $this->jwtService->createToken(['email' => 'check@example.com']);
        self::assertFalse($this->jwtService->check($token, 'wrong_secret'));
    }

    public function testGetPayloadReturnsPayload(): void
    {
        $token = $this->jwtService->createToken(['email' => 'payload@example.com', 'role' => 'admin']);
        $payload = $this->jwtService->getPayload($token);

        self::assertIsArray($payload);
        self::assertSame('payload@example.com', $payload['email']);
        self::assertSame('admin', $payload['role']);
    }

    public function testGetPayloadWithInvalidTokenReturnsNull(): void
    {
        $result = $this->jwtService->getPayload('invalid.token');
        self::assertNull($result);
    }

    public function testGenerateWithCustomSecret(): void
    {
        $customSecret = 'custom_secret_key_for_test_1234567890';
        $token = $this->jwtService->generate([], ['email' => 'gen@example.com'], $customSecret);

        self::assertIsString($token);
        self::assertTrue($this->jwtService->check($token, $customSecret));
        self::assertFalse($this->jwtService->check($token, $this->secret));
    }

    public function testGenerateDoesNotLeakSecret(): void
    {
        $originalToken = $this->jwtService->createToken(['email' => 'original@example.com']);
        $this->jwtService->generate([], ['email' => 'gen@example.com'], 'other_secret_key_long_enough_1234567890');
        $afterToken = $this->jwtService->createToken(['email' => 'after@example.com']);

        self::assertTrue($this->jwtService->check($originalToken, $this->secret));
        self::assertTrue($this->jwtService->check($afterToken, $this->secret));
    }
}
