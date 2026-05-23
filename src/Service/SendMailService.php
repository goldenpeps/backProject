<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SendMailService
{
    private $mailer;
    private $userRepository;

    public function __construct(MailerInterface $mailer, UtilisateurRepository $userRepository)
    {
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
    }


    public function sendMail(Utilisateur $user): void
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];
        $payload = [
            'user_id' => $user->getId(),
        ];
        $this->sendNotifieUpdateUser(
            'no-reply@extravittonclop.com',
            'Modification de l\'email de ' . $user->getPrenom() . ' ' . $user->getNom(),
            'notify_update_email',
            compact('user'),
        );
    }
    public function send(string $from, string $to, string $subject, string $template, array $context): void
    {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("bundles/TwigBundle/mail/$template.html.twig")
            ->textTemplate("bundles/TwigBundle/mail/$template.txt.twig")
            ->context($context);

        $this->mailer->send($email);
    }

    public function sendNotifieUpdateUser(string $from, string $subject, string $template, array $context): void
{
    $notifiedUsers = $this->userRepository->findUserNotified();

    foreach ($notifiedUsers as $userNotifier) {
        /** @var Utilisateur $userNotifier */
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($userNotifier->getEmail())
            ->subject($subject)
            ->htmlTemplate("bundles/TwigBundle/mail/$template.html.twig")
            ->textTemplate("bundles/TwigBundle/mail/$template.txt.twig")
            ->context($context);
        $this->mailer->send($email);
    }
}
}
