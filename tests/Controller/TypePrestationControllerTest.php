<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class TypePrestationControllerTest extends ApiTestCase
{
    public function testGetAllTypePrestation(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/type-prestations/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetTypePrestationById(): void
    {
        $client = $this->createAuthenticatedClient();
        $typePrestation = $this->createTestTypePrestation();
        $client->request('GET', '/api/admin/type-prestations/' . $typePrestation->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetTypePrestationByIdNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/type-prestations/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateTypePrestation(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/type-prestations/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Test Prestation',
            'description' => 'Test Description',
            'prixUnitaire' => 99.99,
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateTypePrestationMissingField(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/type-prestations/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Test Prestation',
            'description' => 'Test Description',
        ]));

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateTypePrestation(): void
    {
        $client = $this->createAuthenticatedClient();
        $typePrestation = $this->createTestTypePrestation();
        $client->request('PUT', '/api/admin/type-prestations/' . $typePrestation->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Updated Prestation',
            'description' => 'Updated Description',
            'prixUnitaire' => 149.99,
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateTypePrestationNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/type-prestations/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Updated Prestation',
        ]));

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteTypePrestation(): void
    {
        $client = $this->createAuthenticatedClient();
        $typePrestation = $this->createTestTypePrestation();
        $client->request('DELETE', '/api/admin/type-prestations/' . $typePrestation->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteTypePrestationNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/admin/type-prestations/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }
}
