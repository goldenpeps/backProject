<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class HistoriqueTerrainControllerTest extends ApiTestCase
{
    public function testGetAllHistoriqueTerrains(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/historique-terrains');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetHistoriqueTerrainById(): void
    {
        $client = $this->createAuthenticatedClient();
        $historique = $this->createTestHistoriqueTerrain();
        $client->request('GET', '/api/admin/historique-terrains/' . $historique->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetHistoriqueTerrainByIdNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/historique-terrains/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateHistoriqueTerrain(): void
    {
        $client = $this->createAuthenticatedClient();
        $terrain = $this->createTestTerrain(false);
        $client->request('POST', '/api/admin/historique-terrain', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'terrain_id' => $terrain->getId(),
            'dateramage' => '2025-02-03',
            'isramassage' => true,
            'dateTonte' => '2025-02-04',
            'istonte' => true,
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateHistoriqueTerrainMissingField(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/historique-terrain', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'dateramage' => '2025-02-03',
            'isramassage' => true,
        ]));

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateHistoriqueTerrain(): void
    {
        $client = $this->createAuthenticatedClient();
        $historique = $this->createTestHistoriqueTerrain();
        $client->request('PUT', '/api/admin/historique-terrain/' . $historique->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'isramassage' => false,
            'istonte' => false,
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateHistoriqueTerrainNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/historique-terrain/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'isramassage' => false,
        ]));

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteHistoriqueTerrain(): void
    {
        $client = $this->createAuthenticatedClient();
        $historique = $this->createTestHistoriqueTerrain();
        $client->request('DELETE', '/api/admin/historique-terrain/' . $historique->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteHistoriqueTerrainNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/admin/historique-terrain/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }
}
