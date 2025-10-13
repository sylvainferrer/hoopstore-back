<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

final class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser(); // ton User
        $payload = $event->getData(); // <-- bonne variable (INIT)

        // Ajoute ce que tu veux dans le payload du token
        $payload['firstname'] = $user->getFirstname();
        $payload['role'] = $user->getRole();

        $event->setData($payload); // <-- on réinjecte
    }
}