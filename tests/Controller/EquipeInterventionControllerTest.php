<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class EquipeInterventionControllerTest extends ApiTestCase
{
    public function testGetEquipeInterventionById(): void
    {
        $client = $this->createAuthenticatedClient();
        $equipe = $this->createTestEquipeIntervention();
        $client->request('GET', '/api/admin/equipe-intervention/' . $equipe->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetEquipeInterventionByIdNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/equipe-intervention/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testSearchEquipeIntervention(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/equipe-intervention/search');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateEquipeIntervention(): void
    {
        $client = $this->createAuthenticatedClient();
        $equipe = $this->createTestEquipeIntervention();
        $client->request('PUT', '/api/admin/equipe-intervention/' . $equipe->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'commentaire' => 'Updated Commentaire',
        ]));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testUpdateEquipeInterventionNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/admin/equipe-intervention/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'commentaire' => 'Updated Commentaire',
        ]));

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteEquipeIntervention(): void
    {
        $client = $this->createAuthenticatedClient();
        $equipe = $this->createTestEquipeIntervention();
        $client->request('DELETE', '/api/admin/equipe-intervention/' . $equipe->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteEquipeInterventionNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/admin/equipe-intervention/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateEquipeIntervention(): void
    {
        $client = $this->createAuthenticatedClient();
        $user = $this->createTestUtilisateur();
        $client->request('POST', '/api/admin/equipe-intervention', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'commentaire' => 'Nouvelle équipe test',
            'utilisateurs' => [$user->getId()],
        ]));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['success']);
        self::assertArrayHasKey('equipeIntervention', $data);
    }

    public function testCreateEquipeInterventionMissingCommentaire(): void
    {
        $client = $this->createAuthenticatedClient();
        $user = $this->createTestUtilisateur();
        $client->request('POST', '/api/admin/equipe-intervention', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'utilisateurs' => [$user->getId()],
        ]));

        self::assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['error']);
    }

    public function testCreateEquipeInterventionWithoutUtilisateur(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/equipe-intervention', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'commentaire' => 'Équipe sans utilisateur',
        ]));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['success']);
    }

    public function testCreateEquipeInterventionInvalidUtilisateur(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/equipe-intervention', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'commentaire' => 'Équipe utilisateur inexistant',
            'utilisateurs' => [999999],
        ]));

        self::assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['error']);
    }

    public function testUpdateEquipeInterventionWithUtilisateurs(): void
    {
        $client = $this->createAuthenticatedClient();
        $equipe = $this->createTestEquipeIntervention();
        $user = $this->createTestUtilisateur();
        $client->request('PUT', '/api/admin/equipe-intervention/' . $equipe->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'commentaire' => 'Équipe avec utilisateur',
            'utilisateurs' => [$user->getId()],
        ]));

        self::assertResponseIsSuccessful();
    }

    public function testUpdateEquipeInterventionWithSingleUtilisateur(): void
    {
        $client = $this->createAuthenticatedClient();
        $equipe = $this->createTestEquipeIntervention();
        $user = $this->createTestUtilisateur();
        $client->request('PUT', '/api/admin/equipe-intervention/' . $equipe->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'utilisateur' => $user->getId(),
        ]));

        self::assertResponseIsSuccessful();
    }

    public function testSearchEquipeInterventionWithFilters(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/equipe-intervention/search?date=2026-01-01');

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
    }
}
