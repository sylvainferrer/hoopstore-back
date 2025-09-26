<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthenticationFailureListener
{
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $err = [
            'message' => 'Vos identifiants sont incorrects ou manquants.'
        ];

        $response = new JsonResponse($err, Response::HTTP_UNAUTHORIZED);
        $event->setResponse($response);
    }
}

