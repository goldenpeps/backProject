<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class MaterielUtiliseControllerTest extends ApiTestCase
{
    public function testGetAllMaterielUtilise(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/materiels-utilises');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetMaterielUtiliseById(): void
    {
        $client = $this->createAuthenticatedClient();
        $materielUtilise = $this->createTestMaterielUtilise();
        $client->request('GET', '/api/admin/materiels-utilises/' . $materielUtilise->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetMaterielUtiliseByIdNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/materiels-utilises/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateMaterielUtilise(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/materiels-utilises', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'durree' => '2026-02-04',
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateMaterielUtiliseMissingField(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/materiels-utilises', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateMaterielUtilise(): void
    {
        $client = $this->createAuthenticatedClient();
        $materielUtilise = $this->createTestMaterielUtilise();
        $client->request('PUT', '/api/admin/materiels-utilises/' . $materielUtilise->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'durree' => '2026-03-01',
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateMaterielUtiliseNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/materiels-utilises/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'durree' => '2026-03-01',
        ]));

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteMaterielUtilise(): void
    {
        $client = $this->createAuthenticatedClient();
        $materielUtilise = $this->createTestMaterielUtilise();
        $client->request('DELETE', '/api/admin/materiels-utilises/' . $materielUtilise->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteMaterielUtiliseNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/admin/materiels-utilises/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }
}
