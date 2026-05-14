<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class ClientControllerTest extends ApiTestCase
{
    public function testGetAllClients(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/clients');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetClientById(): void
    {
        $client = $this->createAuthenticatedClient();
        $testClient = $this->createTestClient();
        $client->request('GET', '/api/admin/client/' . $testClient->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetClientByIdNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/client/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateClient(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/client', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean.dupont' . uniqid() . '@example.com',
            'telephone' => '0612345678',
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateClientMissingField(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/client', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
        ]));

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateClient(): void
    {
        $client = $this->createAuthenticatedClient();
        $testClient = $this->createTestClient();
        $client->request('PUT', '/api/admin/client/' . $testClient->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Martin',
            'telephone' => '0698765432',
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateClientNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/client/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Martin',
        ]));

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }
}
