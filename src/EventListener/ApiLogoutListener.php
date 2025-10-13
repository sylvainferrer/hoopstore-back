<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class ApiLogoutListener
{
    public function onLogout(LogoutEvent $event): void
        {
            $response = new JsonResponse(['message' => 'Déconnexion effectuée avec succès.'], JsonResponse::HTTP_OK);

            $event->setResponse($response);
        }
}
