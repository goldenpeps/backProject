<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthTest extends WebTestCase
{
    private function getTestEmail(): string
    {
        return 'test' . uniqid() . '@example.com';
    }

    /**
     * Test: Register a new user successfully
     * Expected: 201 Created with user data
     */
    public function testRegisterSuccess(): void
    {
        $client = static::createClient();
        $email = $this->getTestEmail();
        
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => 'Password123!',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '0123456789',
          
        ]));

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($json['success']);
        $this->assertSame('Utilisateur créé avec succès', $json['message']);
        $this->assertSame($email, $json['user']['email']);
    }

    /**
     * Test: Try to register with invalid email format
     * Expected: 400 Bad Request
     */
    public function testRegisterInvalidEmail(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '0123456789',
          
        ]));

        $this->assertResponseStatusCodeSame(400);
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($json['error']);
        $this->assertSame('Email invalide', $json['message']);
    }

    /**
     * Test: Try to register with password too short
     * Expected: 400 Bad Request
     */
    public function testRegisterPasswordTooShort(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $this->getTestEmail(),
            'password' => 'short',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '0123456789',
          
        ]));

        $this->assertResponseStatusCodeSame(400);
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($json['error']);
        $this->assertSame('Le mot de passe doit contenir au moins 8 caractères', $json['message']);
    }

    /**
     * Test: Try to register with email that already exists
     * Expected: 409 Conflict
     */
    public function testRegisterDuplicateEmail(): void
    {
        $client = static::createClient();
        $email = $this->getTestEmail();
        
        // First registration
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => 'Password123!',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '0123456789',
          
        ]));

        $this->assertResponseStatusCodeSame(201);

        // Second registration with same email
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => 'AnotherPassword123!',
            'nom' => 'Martin',
            'prenom' => 'Pierre',
            'telephone' => '0987654321',
          
        ]));

        $this->assertResponseStatusCodeSame(409);
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($json['error']);
        $this->assertSame('Cet email est déjà utilisé', $json['message']);
    }

    /**
     * Test: Try to register with missing required fields
     * Expected: 400 Bad Request
     */
    public function testRegisterMissingFields(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $this->getTestEmail(),
            'password' => 'Password123!',
            // Missing nom, prenom, telephone
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * Test: Login with valid credentials
     * Expected: 200 OK with JWT token
     */
    public function testLoginSuccess(): void
    {
        $client = static::createClient();
        $email = $this->getTestEmail();
        $password = 'Password123!';
        
        // First, register a user
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => $password,
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '0123456789',
          
        ]));

        $this->assertResponseStatusCodeSame(201);

        // Now, login
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        $this->assertResponseIsSuccessful();
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($json['success']);
        $this->assertSame('Connexion réussie', $json['message']);
        $this->assertSame($email, $json['user']['email']);
        $this->assertArrayHasKey('token', $json);
        $this->assertIsString($json['token']);
    }

    /**
     * Test: Login with invalid email
     * Expected: 401 Unauthorized
     */
    public function testLoginInvalidEmail(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'nonexistent' . uniqid() . '@example.com',
            'password' => 'SomePassword123!',
        ]));

        $this->assertResponseStatusCodeSame(401);
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($json['error']);
        $this->assertSame('Identifiants invalides', $json['message']);
    }

    /**
     * Test: Login with invalid password
     * Expected: 401 Unauthorized
     */
    public function testLoginInvalidPassword(): void
    {
        $client = static::createClient();
        $email = $this->getTestEmail();
        
        // Register a user first
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => 'Password123!',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '0123456789',
          
        ]));

        // Try login with wrong password
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => 'WrongPassword123!',
        ]));

        $this->assertResponseStatusCodeSame(401);
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($json['error']);
        $this->assertSame('Identifiants invalides', $json['message']);
    }

    /**
     * Test: Login with missing fields
     * Expected: 400 Bad Request
     */
    public function testLoginMissingFields(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $this->getTestEmail(),
            // Missing password
        ]));

        $this->assertResponseStatusCodeSame(400);
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($json['error']);
        $this->assertSame('Email et mot de passe requis', $json['message']);
    }

    /**
     * Test: Access /api/me without authentication
     * Expected: 401 Unauthorized
     */
    public function testGetMeUnauthorized(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/me');

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Test: Access /api/me with valid JWT token
     * Expected: 200 OK with user data
     */
    public function testGetMeAuthorized(): void
    {
        $client = static::createClient();
        $email = $this->getTestEmail();
        $nom = 'Dupont';
        $prenom = 'Jean';
        $telephone = '0123456789';
        
        // Register a user
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => 'Password123!',
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
        ]));

        // Login to get token
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => 'Password123!',
        ]));

        $loginResponse = json_decode($client->getResponse()->getContent(), true);
        $token = $loginResponse['token'];

        // Access /api/me with token
        $client->request('GET', '/api/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($json['success']);
        $this->assertSame($email, $json['user']['email']);
        $this->assertSame($nom, $json['user']['nom']);
        $this->assertSame($prenom, $json['user']['prenom']);
        $this->assertSame($telephone, $json['user']['telephone']);
    }

    /**
     * Test: Access /api/me with invalid JWT token
     * Expected: 401 Unauthorized
     */
    public function testGetMeInvalidToken(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer invalid_token_here',
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }
}     
