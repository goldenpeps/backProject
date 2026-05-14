<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class TerrainControllerTest extends ApiTestCase
{
    public function testGetAllTerrains(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/terrain/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetTerrainById(): void
    {
        $client = $this->createAuthenticatedClient();
        $terrain = $this->createTestTerrain();
        $client->request('GET', '/api/admin/terrain/' . $terrain->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetTerrainByIdNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/terrain/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetTerrainsByClient(): void
    {
        $client = $this->createAuthenticatedClient();
        $testClient = $this->createTestClient();
        $client->request('GET', '/api/admin/terrain/client/' . $testClient->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetTerrainsByClientNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/terrain/client/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateTerrain(): void
    {
        $client = $this->createAuthenticatedClient();
        $testClient = $this->createTestClient();
        $typeTerrain = $this->createTestTypeTerrain();
        $client->request('POST', '/api/admin/terrain/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'client_id' => $testClient->getId(),
            'type_terrain_id' => $typeTerrain->getId(),
            'superficie' => 1500.00,
            'commentaire' => 'Test terrain creation',
            'historique_terrain' => [
                [
                    'isramassage' => true,
                    'dateramage' => '2025-02-03',
                    'istonte' => true,
                    'dateTonte' => '2025-02-04',
                ]
            ],
            'adresse' => [
                'nom' => 'Parcelle A',
                'cp' => '44000',
                'adresse' => '12 rue des Lilas',
            ],
            'coordonnees_gps' => [
                'latitude' => 47.2184,
                'longitude' => -1.5536,
            ],
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('Parcelle A', $responseData['terrain']['adresse']['nom']);
        self::assertSame('44000', $responseData['terrain']['adresse']['cp']);
        self::assertSame(47.2184, $responseData['terrain']['coordonnees_gps']['latitude']);
    }

    public function testCreateTerrainMissingField(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/terrain/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'superficie' => 1500.00,
        ]));

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateTerrain(): void
    {
        $client = $this->createAuthenticatedClient();
        $terrain = $this->createTestTerrain();
        $client->request('PUT', '/api/admin/terrain/' . $terrain->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'superficie' => 2000.00,
            'commentaire' => 'Updated terrain',
            'adresse' => [
                'nom' => 'Parcelle B',
                'cp' => '33000',
                'adresse' => '8 avenue du Port',
            ],
            'coordonnees_gps' => [
                'latitude' => 44.8378,
                'longitude' => -0.5792,
            ],
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('Parcelle B', $responseData['terrain']['adresse']['nom']);
        self::assertSame(44.8378, $responseData['terrain']['coordonnees_gps']['latitude']);
    }

    public function testUpdateTerrainNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/terrain/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'superficie' => 2000.00,
        ]));

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteTerrain(): void
    {
        $client = $this->createAuthenticatedClient();
        $terrain = $this->createTestTerrain();
        $client->request('DELETE', '/api/admin/terrain/' . $terrain->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteTerrainNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/admin/terrain/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }
}
