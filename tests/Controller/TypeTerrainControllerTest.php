<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class TypeTerrainControllerTest extends ApiTestCase
{
    public function testGetAllTypeTerrain(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/type-terrain');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetTypeTerrainById(): void
    {
        $client = $this->createAuthenticatedClient();
        $typeTerrain = $this->createTestTypeTerrain();
        $client->request('GET', '/api/admin/type-terrain/' . $typeTerrain->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetTypeTerrainByIdNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/type-terrain/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateTypeTerrain(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/type-terrain', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Test Terrain',
            'description' => 'Test Description',
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateTypeTerrainMissingField(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/type-terrain', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Test Terrain',
        ]));

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateTypeTerrain(): void
    {
        $client = $this->createAuthenticatedClient();
        $typeTerrain = $this->createTestTypeTerrain();
        $client->request('PUT', '/api/admin/type-terrain/' . $typeTerrain->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Updated Terrain',
            'description' => 'Updated Description',
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateTypeTerrainNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/type-terrain/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Updated Terrain',
        ]));

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteTypeTerrain(): void
    {
        $client = $this->createAuthenticatedClient();
        $typeTerrain = $this->createTestTypeTerrain();
        $client->request('DELETE', '/api/admin/type-terrain/' . $typeTerrain->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteTypeTerrainNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/admin/type-terrain/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }
}
