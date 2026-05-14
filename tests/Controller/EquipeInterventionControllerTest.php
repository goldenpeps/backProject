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
}
