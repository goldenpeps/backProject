<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class LigneDevisControllerTest extends ApiTestCase
{
    public function testGetLigneDevisByDevis(): void
    {
        $client = $this->createAuthenticatedClient();
        $devis = $this->createTestDevis();
        $client->request('GET', '/api/admin/ligne-devis/' . $devis->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetLigneDevisByDevisNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/ligne-devis/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateLigneDevis(): void
    {
        $client = $this->createAuthenticatedClient();
        $devis = $this->createTestDevis();
        $typePrestation = $this->createTestTypePrestation();
        $client->request('POST', '/api/admin/ligne-devis', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'devisId' => $devis->getId(),
            'typePrestationId' => $typePrestation->getId(),
            'quantite' => 3,
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateLigneDevisMissingField(): void
    {
        $client = $this->createAuthenticatedClient();
        $devis = $this->createTestDevis();
        $client->request('POST', '/api/admin/ligne-devis', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'devisId' => $devis->getId(),
        ]));

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateLigneDevis(): void
    {
        $client = $this->createAuthenticatedClient();
        $ligneDevis = $this->createTestLigneDevis();
        $client->request('PUT', '/api/admin/ligne-devis/' . $ligneDevis->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'quantite' => 5,
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateLigneDevisNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/ligne-devis/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'quantite' => 5,
        ]));

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteLigneDevis(): void
    {
        $client = $this->createAuthenticatedClient();
        $ligneDevis = $this->createTestLigneDevis();
        $client->request('DELETE', '/api/admin/ligne-devis/' . $ligneDevis->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteLigneDevisNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/admin/ligne-devis/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }
}
