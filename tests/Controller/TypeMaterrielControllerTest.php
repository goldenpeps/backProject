<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class TypeMaterrielControllerTest extends ApiTestCase
{
    public function testGetAllTypeMateriel(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/type-materriel/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetTypeMaterielById(): void
    {
        $client = $this->createAuthenticatedClient();
        $typeMateriel = $this->createTestTypeMateriel();
        $client->request('GET', '/api/admin/type-materriel/' . $typeMateriel->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetTypeMaterielByIdNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/type-materriel/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateTypeMateriel(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/type-materriel/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'libelle' => 'Test Material',
            'transportable' => true,
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateTypeMaterielMissingField(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/type-materriel/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'libelle' => 'Test Material',
        ]));

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateTypeMateriel(): void
    {
        $client = $this->createAuthenticatedClient();
        $typeMateriel = $this->createTestTypeMateriel();
        $client->request('PUT', '/api/admin/type-materriel/' . $typeMateriel->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'libelle' => 'Updated Material',
            'transportable' => false,
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateTypeMaterielNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/type-materriel/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'libelle' => 'Updated Material',
        ]));

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteTypeMateriel(): void
    {
        $client = $this->createAuthenticatedClient();
        $typeMateriel = $this->createTestTypeMateriel();
        $client->request('DELETE', '/api/admin/type-materriel/' . $typeMateriel->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteTypeMaterielNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/admin/type-materriel/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }
}
