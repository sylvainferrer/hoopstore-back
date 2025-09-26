<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTFailureEventInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response; 

class JWTFailureListener
{
    /**
     * Méthode appelée pour tout échec JWT :
     * - JWT not found
     * - JWT invalid
     * - JWT expired
     */
    public function onJwtFailure(JWTFailureEventInterface $event)
    {
        $err = [
            'message' => 'Votre session a expiré ou vos informations d’accès sont manquantes ou invalides. Merci de vous reconnecter.'
        ];

        $response = new JsonResponse($err, Response::HTTP_UNAUTHORIZED);
        $event->setResponse($response);
    }
}
