<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Event\ContactRequestEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class MailingSubscriber implements EventSubscriberInterface
{

    function __construct(
        private readonly MailerInterface $mailer
    )
    {
    }

    function onContactRequestEvent(ContactRequestEvent $event) : void
    {
        $data = $event->getData();
        $mail = (new TemplatedEmail())
            ->to($data->getService())
            ->from($data->getEmail())
            ->subject('Nouvelle demande de contact')
            ->context(['data' => $data])
            ->htmlTemplate('emails/contact.html.twig')
        ;
        $this->mailer->send($mail);
    }

    function onLogin(InteractiveLoginEvent $event) : void
    {
        /**
         * @var User $user
         */
        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof User) return;
        $mail = (new Email())
            ->to($user->getEmail())
            ->from('support@demo.fr')
            ->subject('Nouvelle connexion détectée')
            ->text('Une nouvelle connexion vient d\'être détectée avec votre compte')
        ;
        $this->mailer->send($mail);
    }

    function onLogout(LogoutEvent $event) : void
    {
        /**
         * @var User $user
         */
        $user = $event->getToken()->getUser();
        if (!$user instanceof User) return;
        $mail = (new Email())
            ->to($user->getEmail())
            ->from('support@demo.fr')
            ->subject('Déconnexion détectée')
            ->text('Vous venez de vous déconnecter')
        ;
        $this->mailer->send($mail);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContactRequestEvent::class => 'onContactRequestEvent',
            /* InteractiveLoginEvent::class => 'onLogin',
            LogoutEvent::class => 'onLogout' */
        ];
    }
}
