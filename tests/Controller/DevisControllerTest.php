<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class DevisControllerTest extends ApiTestCase
{
    public function testGetAllDevis(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/devis');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetDevisById(): void
    {
        $client = $this->createAuthenticatedClient();
        $devis = $this->createTestDevis();
        $client->request('GET', '/api/admin/devis/' . $devis->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetDevisByIdNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/devis/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateDevis(): void
    {
        $client = $this->createAuthenticatedClient();
        $testClient = $this->createTestClient();
        $client->request('POST', '/api/admin/devis', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'clientId' => $testClient->getId(),
            'dateDevis' => '2026-02-04',
            'montantTotal' => 1500.00,
            'status' => 'en_attente',
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateDevisMissingField(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/devis', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'dateDevis' => '2026-02-04',
            'montantTotal' => 1500.00,
        ]));

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateDevis(): void
    {
        $client = $this->createAuthenticatedClient();
        $devis = $this->createTestDevis();
        $client->request('PUT', '/api/admin/devis/' . $devis->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'montantTotal' => 2000.00,
            'status' => 'valide',
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateDevisNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/devis/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'montantTotal' => 2000.00,
        ]));

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testAnnulerDevis(): void
    {
        $client = $this->createAuthenticatedClient();
        $devis = $this->createTestDevis();
        $client->request('PUT', '/api/admin/devis/annuler/' . $devis->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testAnnulerDevisNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/devis/annuler/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }
}
