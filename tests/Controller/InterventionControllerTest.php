<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class InterventionControllerTest extends ApiTestCase
{
    public function testGetAllInterventions(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/interventions');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateIntervention(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/interventions', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'datePrevue' => '2026-02-10 10:00:00',
            'dateRealisation' => '2026-02-10 12:00:00',
            'commentaire' => 'Test Intervention',
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateInterventionMissingField(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/interventions', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'datePrevue' => '2026-02-10',
        ]));

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateIntervention(): void
    {
        $client = $this->createAuthenticatedClient();
        $intervention = $this->createTestIntervention();
        $client->request('PUT', '/api/admin/intervention/' . $intervention->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'commentaire' => 'Updated Intervention',
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateInterventionNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/intervention/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'commentaire' => 'Updated Intervention',
        ]));

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteIntervention(): void
    {
        $client = $this->createAuthenticatedClient();
        $intervention = $this->createTestIntervention();
        $client->request('DELETE', '/api/admin/intervention/' . $intervention->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteInterventionNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/admin/intervention/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }
}
