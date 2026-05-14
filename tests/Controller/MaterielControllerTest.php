<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class MaterielControllerTest extends ApiTestCase
{
    public function testGetAllMateriel(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/materiel');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetMaterielById(): void
    {
        $client = $this->createAuthenticatedClient();
        $materiel = $this->createTestMateriel();
        $client->request('GET', '/api/admin/materiel/' . $materiel->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetMaterielByIdNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/materiel/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateMateriel(): void
    {
        $client = $this->createAuthenticatedClient();
        $typeMateriel = $this->createTestTypeMateriel();
        $client->request('POST', '/api/admin/materiel', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'disponible' => true,
            'typeMaterielId' => $typeMateriel->getId(),
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateMaterielMissingField(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/materiel', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'disponible' => true,
        ]));

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateMateriel(): void
    {
        $client = $this->createAuthenticatedClient();
        $materiel = $this->createTestMateriel();
        $typeMateriel = $this->createTestTypeMateriel();
        $client->request('PUT', '/api/admin/materiel/' . $materiel->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'disponible' => false,
            'typeMaterielId' => $typeMateriel->getId(),
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateMaterielNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/materiel/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'disponible' => false,
        ]));

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteMateriel(): void
    {
        $client = $this->createAuthenticatedClient();
        $materiel = $this->createTestMateriel();
        $client->request('DELETE', '/api/admin/materiel/' . $materiel->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteMaterielNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/admin/materiel/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }
}
