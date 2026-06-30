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

    public function testGetMyPlanning(): void
    {
        $client = $this->createAuthenticatedClient(['email' => 'planning_test@example.com']);
        $client->request('GET', '/api/planning/me');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
    }

    public function testGetMyPlanningWithDateFilter(): void
    {
        $client = $this->createAuthenticatedClient(['email' => 'planning_filter@example.com']);
        $client->request('GET', '/api/planning/me?from=2026-01-01&to=2026-12-31');

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
    }

    public function testGetMyPlanningWithInvalidFromDate(): void
    {
        $client = $this->createAuthenticatedClient(['email' => 'planning_badfrom@example.com']);
        $client->request('GET', '/api/planning/me?from=invalid-date');

        self::assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['error']);
    }

    public function testGetMyPlanningWithInvalidToDate(): void
    {
        $client = $this->createAuthenticatedClient(['email' => 'planning_badto@example.com']);
        $client->request('GET', '/api/planning/me?to=invalid-date');

        self::assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['error']);
    }

    public function testGetMyPlanningUnauthorized(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/planning/me');

        self::assertResponseStatusCodeSame(401);
    }

    public function testCreateInterventionInvalidDateFormat(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/interventions', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'datePrevue' => 'not-a-date',
            'dateRealisation' => '2026-02-10 12:00:00',
            'commentaire' => 'Test',
        ]));

        self::assertResponseStatusCodeSame(400);
    }

    public function testCreateInterventionEndBeforeStart(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/interventions', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'datePrevue' => '2026-02-10 12:00:00',
            'dateRealisation' => '2026-02-10 10:00:00',
            'commentaire' => 'Test invalid dates',
        ]));

        self::assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['error']);
    }
}
