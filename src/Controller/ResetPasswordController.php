<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/api/password')]
final class ResetPasswordController extends AbstractController
{
    public function __construct(
        private ResetPasswordHelperInterface $helper,
        private EntityManagerInterface $em,
        private UserRepository $users,
    ) {}

    #[Route('/forgot', name: 'api_password_forgot', methods: ['POST'])]
    public function forgot(Request $request, MailerInterface $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $email = isset($data['email']) && is_string($data['email']) ? $data['email'] : '';

        if ($email === '') {
            return new JsonResponse(['message' => 'Si un compte existe, un email a été envoyé.'], JsonResponse::HTTP_OK);
        }

        $user = $this->users->findOneBy(['email' => $email]);

        if (!$user) {
            // Optionnel : usleep(random_int(20_000, 60_000));
            return new JsonResponse(['message' => 'Si un compte existe, un email a été envoyé.'], JsonResponse::HTTP_OK);
        }

        try {
            $resetToken = $this->helper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return new JsonResponse(['message' => 'Si un compte existe, un email a été envoyé.'], JsonResponse::HTTP_OK);
        }

        $link  = $_ENV['APP_FRONT_URL'] . '/password/reset/' . $resetToken->getToken();

            $from = $_ENV['MAILER_FROM'] ?? 'HoopStore <noreply@localhost>';
            $mailer->send(
                (new Email())
                    ->from($from)
                    ->to($email)
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html(
                        '<p>Vous avez demandé une réinitialisation de mot de passe.</p>'
                        . '<p><a href="' . htmlspecialchars($link, ENT_QUOTES) . '">Cliquez ici pour réinitialiser</a> (valable 1h).</p>'
                        . '<p>Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.</p>'
                    )
            );

        return new JsonResponse(['message' => 'Si un compte existe, un email a été envoyé.'], JsonResponse::HTTP_OK);
    }

    #[Route('/reset', name: 'api_password_reset', methods: ['POST'])]
    public function reset(Request $request, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $token = isset($data['token']) && is_string($data['token']) ? $data['token'] : '';
        $newPassword = isset($data['password']) && is_string($data['password']) ? $data['password'] : '';

        if (strlen($newPassword) < 1) {
            return new JsonResponse(['message' => 'Mot de passe trop court (min 12).'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->helper->validateTokenAndFetchUser($token);
        } catch (\Throwable $e) {
            return new JsonResponse(['message' => 'Lien invalide ou expiré.'], JsonResponse::HTTP_GONE);
        }

        // Hash + set le nouveau mot de passe
        $user->setPassword($hasher->hashPassword($user, $newPassword));

        // Invalider ce token (usage unique)
        $this->helper->removeResetRequest($token);

        $this->em->flush();

        return new JsonResponse(['message' => 'Mot de passe réinitialisé.'], JsonResponse::HTTP_OK);
    }
}