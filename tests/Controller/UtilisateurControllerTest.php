<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

final class UtilisateurControllerTest extends ApiTestCase
{
    public function testGetAllUtilisateurs(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/utilisateurs');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
    }

    public function testGetUtilisateurById(): void
    {
        $client = $this->createAuthenticatedClient();
        $user = $this->createTestUtilisateur();
        $client->request('GET', '/api/admin/utilisateurs/' . $user->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame($user->getEmail(), $data['email']);
    }

    public function testGetUtilisateurByIdNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/admin/utilisateurs/999999');

        self::assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['error']);
        self::assertSame('Utilisateur non trouvé', $data['message']);
    }

    public function testDeactivateUtilisateur(): void
    {
        $client = $this->createAuthenticatedClient();
        $user = $this->createTestUtilisateur();
        $client->request('POST', '/api/admin/utilisateurs/' . $user->getId());

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['success']);
    }

    public function testDeactivateUtilisateurNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/admin/utilisateurs/999999');

        self::assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['error']);
    }

    public function testDeactivateOwnAccountForbidden(): void
    {
        $client = $this->createAuthenticatedClient(['email' => 'selftest@example.com']);
        $em = self::getEntityManager();
        $user = $em->getRepository(\App\Entity\Utilisateur::class)->findOneBy(['email' => 'selftest@example.com']);

        $client->request('POST', '/api/admin/utilisateurs/' . $user->getId());

        self::assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['error']);
        self::assertSame('Vous ne pouvez pas désactiver votre propre compte', $data['message']);
    }

    public function testUpdateUtilisateur(): void
    {
        $client = $this->createAuthenticatedClient(['email' => 'admin_update@example.com', 'roles' => ['ROLE_ADMIN', 'ROLE_USER']]);
        $user = $this->createTestUtilisateur();
        $client->request('PUT', '/api/admin/utilisateurs/' . $user->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Nouveau Nom',
            'prenom' => 'Nouveau Prenom',
            'telephone' => '0987654321',
        ]));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['success']);
    }

    public function testUpdateUtilisateurNotFound(): void
    {
        $client = $this->createAuthenticatedClient(['email' => 'admin_nf@example.com', 'roles' => ['ROLE_ADMIN', 'ROLE_USER']]);
        $client->request('PUT', '/api/admin/utilisateurs/999999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'nom' => 'Nouveau Nom',
        ]));

        self::assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['error']);
    }

    public function testUpdateUtilisateurRoles(): void
    {
        $client = $this->createAuthenticatedClient(['email' => 'admin_roles@example.com', 'roles' => ['ROLE_ADMIN', 'ROLE_USER']]);
        $user = $this->createTestUtilisateur();
        $client->request('PUT', '/api/admin/utilisateurs/' . $user->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'roles' => ['ROLE_ADMIN', 'ROLE_USER'],
        ]));

        self::assertResponseIsSuccessful();
    }

    public function testUpdateUtilisateurEmail(): void
    {
        $client = $this->createAuthenticatedClient(['email' => 'admin_email@example.com', 'roles' => ['ROLE_ADMIN', 'ROLE_USER']]);
        $user = $this->createTestUtilisateur();
        $newEmail = 'updated_' . uniqid() . '@example.com';
        $client->request('PUT', '/api/admin/utilisateurs/' . $user->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $newEmail,
        ]));

        self::assertResponseIsSuccessful();
    }
}
