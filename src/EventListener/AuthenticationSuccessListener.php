<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener
{
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)

    {
        $data = $event->getData();

        // On ajoute juste un message de succès
        $data['message'] = 'Connexion réussie !';

        $event->setData($data);
    }
}