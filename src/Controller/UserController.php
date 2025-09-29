<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api')]
final class UserController extends AbstractController
{
    /**
     * INSCRIPTION D'UN NOUVEL UTILISATEUR
     */
    #[Route('/users/register', name: 'api_user_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        $user = new User();

        $lastname = isset($data['lastname']) && is_string($data['lastname']) ? $data['lastname'] : null;
        $firstname = isset($data['firstname']) && is_string($data['firstname']) ? $data['firstname'] : null;
        $birthday = is_string($data['birthday'] ?? null) && ($parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $data['birthday'])) && $parsed->format('Y-m-d') === $data['birthday'] ? $parsed : null;
        $email = isset($data['email']) && is_string($data['email']) ? $data['email'] : null;
        $adresse = isset($data['adresse']) && is_string($data['adresse']) ? $data['adresse'] : null;
        $codePostal = isset($data['codePostal']) && is_string($data['codePostal']) ? $data['codePostal'] : null;
        $ville = isset($data['ville']) && is_string($data['ville']) ? $data['ville'] : null;
        $role = (isset($data['role']) && is_string($data['role'])) ? $data['role'] : 'ROLE_USER';
        // $password = null;
        // if (isset($data['password']) && is_string($data['password']) && trim($data['password']) !== '') {
        //     $password = $passwordHasher->hashPassword($user, $data['password']);
        // }
        $password = (isset($data['password']) && is_string($data['password']) && trim($data['password']) !== '') ? $data['password'] : null;

        $user->setLastname($lastname);
        $user->setFirstname($firstname);
        $user->setBirthday($birthday);
        $user->setEmail($email);
        $user->setAdresse($adresse);
        $user->setCodePostal($codePostal);
        $user->setVille($ville);
        $user->setRole($role);
        $user->setPassword($password);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($messages, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $hash = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hash);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message' => 'Votre compte a bien été créé.'], JsonResponse::HTTP_CREATED);
    }


    /**
     * AFFICHER LES INFOS DE L'UTILISATEUR CONNECTÉ
     */
    #[Route('/users/me', name: 'api_user_show', methods: ['GET'])]
    public function showMe(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || !$user instanceof User) {
            return new JsonResponse(['message' => 'Utilisateur introuvable ou invalide.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'lastname'  => $user->getLastname(),
            'firstname' => $user->getFirstname(),
            'birthday'  => $user->getBirthday()->format('Y-m-d'),
            'email'     => $user->getEmail(),
            'adresse'   => $user->getAdresse(),
            'codePostal' => $user->getCodePostal(),
            'role'       => $user->getRole(),
            'ville'     => $user->getVille()

        ], JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    /**
     * MODIFIER LES INFOS DE L'UTILISATEUR CONNECTÉ
     */
    #[Route('/users/me', name: 'api_user_update', methods: ['PUT'])]
    public function updateMe(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {

        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (array_key_exists('lastname', $data)) {
            $user->setLastname(is_string($data['lastname']) && $data['lastname'] !== '' ? $data['lastname'] : null);
        }
        if (array_key_exists('firstname', $data)) {
            $user->setFirstname(is_string($data['firstname']) && $data['firstname'] !== '' ? $data['firstname'] : null);
        }
        if (array_key_exists('birthday', $data)) {
            $parsed = (is_string($data['birthday']) && $data['birthday'] !== '') ? \DateTimeImmutable::createFromFormat('Y-m-d', $data['birthday']) : null;
            $user->setBirthday($parsed && $parsed->format('Y-m-d') === $data['birthday'] ? $parsed : null);
        }
        if (array_key_exists('email', $data)) {
            $user->setEmail(is_string($data['email']) && $data['email'] !== '' ? $data['email'] : '');
        }
        if (array_key_exists('adresse', $data)) {
            $user->setAdresse(is_string($data['adresse']) && $data['adresse'] !== '' ? $data['adresse'] : null);
        }
        if (array_key_exists('codePostal', $data)) {
            $user->setCodePostal(is_string($data['codePostal']) && $data['codePostal'] !== '' ? $data['codePostal'] : null);
        }
        if (array_key_exists('ville', $data)) {
            $user->setVille(is_string($data['ville']) && $data['ville'] !== '' ? $data['ville'] : null);
        }
        if (array_key_exists('role', $data)) {
            $user->setRole(is_string($data['role']) && $data['role'] !== '' ? $data['role'] : 'ROLE_USER');
        }
        // if (array_key_exists('password', $data)) {
        //     $user->setPassword(is_string($data['password']) && $data['password'] !== '' ? $passwordHasher->hashPassword($user, $data['password']) : null);
        // }
        // if (array_key_exists('password', $data)) {
        //     $password = (is_string($data['password']) && $data['password'] !== '' ? $data['password'] : null);
        //     $user->setPassword($password);
        // }
        $password = (array_key_exists('password', $data) && is_string($data['password']) && $data['password'] !== '' ) ? $data['password'] : null;
        $user->setPassword($password);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($messages, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($password !== null) {
            $hash = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hash);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Profil mis à jour avec succès.'], JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    /**
     * SUPPRIMER LE COMPTE DE L'UTILISATEUR CONNECTÉ
     * 
     * !!!!! VÉRIFIER SI 'setCookie()' EST ALIGNÉ AVEC 'set_cookies' DANS LE FICHIER 'lexik_jwt_authentification.yaml'
     */
    #[Route('/users/me', name: 'api_user_delete_me', methods: ['DELETE'])]
    public function deleteMe(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || !$user instanceof User) {
            return new JsonResponse(['message' => 'Utilisateur introuvable ou invalide.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        $response = new JsonResponse(['message' => 'Compte supprimé avec succès.'], JsonResponse::HTTP_OK);

        $response->headers->setCookie(
            Cookie::create('BEARER')
                ->withValue('')
                ->withExpires(0)
                ->withPath('/')
                ->withSecure(false)
                ->withHttpOnly(false)
                ->withSameSite('lax')
        );
        $response->headers->set('Clear-Site-Data', '"storage", "cookies"');

        return $response;
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    /**
     * ADMIN - LISTER TOUS LES UTILISATEURS
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/users', name: 'api_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {

        $users = $userRepository->findAllExceptSuperAdmin();

        $data = array_map(fn(User $user) => [
            'id'         => $user->getId(),
            'lastname'   => $user->getLastname(),
            'firstname'  => $user->getFirstname(),
            'birthday'   => $user->getBirthday()?->format('Y-m-d'),
            'email'      => $user->getEmail(),
            'adresse'    => $user->getAdresse(),
            'codePostal' => $user->getCodePostal(),
            'ville'      => $user->getVille(),
            'role'       => $user->getRole(),
        ], $users);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    /**
     * ADMIN - CRÉER UN UTILISATEUR
     */
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/users', name: 'api_admin_user_create', methods: ['POST'])]
    public function createAdmin(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        $user = new User();

        $lastname = isset($data['lastname']) && is_string($data['lastname']) ? $data['lastname'] : null;
        $firstname = isset($data['firstname']) && is_string($data['firstname']) ? $data['firstname'] : null;
        $birthday = is_string($data['birthday'] ?? null) && ($parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $data['birthday'])) && $parsed->format('Y-m-d') === $data['birthday'] ? $parsed : null;
        $email = isset($data['email']) && is_string($data['email']) ? $data['email'] : null;
        $adresse = isset($data['adresse']) && is_string($data['adresse']) ? $data['adresse'] : null;
        $codePostal = isset($data['codePostal']) && is_string($data['codePostal']) ? $data['codePostal'] : null;
        $ville = isset($data['ville']) && is_string($data['ville']) ? $data['ville'] : null;
        $role = isset($data['role']) && is_string($data['role']) ? $data['role'] : 'ROLE_USER';                
        $password = null;
        // if (isset($data['password']) && is_string($data['password']) && trim($data['password']) !== '') {
        //     $password = $passwordHasher->hashPassword($user, $data['password']);
        // }
        $password = (isset($data['password']) && is_string($data['password']) && trim($data['password']) !== '') ? $data['password'] : null;

        $user->setLastname($lastname);
        $user->setFirstname($firstname);
        $user->setBirthday($birthday);
        $user->setEmail($email);
        $user->setAdresse($adresse);
        $user->setCodePostal($codePostal);
        $user->setVille($ville);
        $user->setRole($role);
        $user->setPassword($password);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($messages, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

          if ($password !== null) {
            $hash = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hash);
        }

        // if (in_array(Roles::SUPER_ADMIN->value, $user->getRoles())) {
        //     return new JsonResponse(['message' => 'Impossible de créer un super admin via l\'interface.'], JsonResponse::HTTP_FORBIDDEN);
        // }

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur créé avec succès.'], JsonResponse::HTTP_CREATED);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    /**
     * ADMIN - AFFICHER UN UTILISATEUR
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/users/{id}', name: 'api_admin_user_show', methods: ['GET'])]
    public function showAdmin(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id'        => $user->getId(),
            'lastname'  => $user->getLastname(),
            'firstname' => $user->getFirstname(),
            'birthday'  => $user->getBirthday()?->format('Y-m-d'),
            'email'     => $user->getEmail(),
            'adresse'   => $user->getAdresse(),
            'codePostal' => $user->getCodePostal(),
            'ville'     => $user->getVille(),
            'role'    => $user->getRole(),
        ], JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    /**
     * ADMIN - MODIFIER UN UTILISATEUR
     */
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/users/{id}', name: 'api_admin_user_update', methods: ['PUT'])]
    public function updateAdmin(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (array_key_exists('lastname', $data)) {
            $user->setLastname(is_string($data['lastname']) && $data['lastname'] !== '' ? $data['lastname'] : null);
        }
        if (array_key_exists('firstname', $data)) {
            $user->setFirstname(is_string($data['firstname']) && $data['firstname'] !== '' ? $data['firstname'] : null);
        }
        if (array_key_exists('birthday', $data)) {
            $parsed = (is_string($data['birthday']) && $data['birthday'] !== '') ? \DateTimeImmutable::createFromFormat('Y-m-d', $data['birthday']) : null;
            $user->setBirthday($parsed && $parsed->format('Y-m-d') === $data['birthday'] ? $parsed : null);
        }
        if (array_key_exists('email', $data)) {
            $user->setEmail(is_string($data['email']) && $data['email'] !== '' ? $data['email'] : '');
        }
        if (array_key_exists('adresse', $data)) {
            $user->setAdresse(is_string($data['adresse']) && $data['adresse'] !== '' ? $data['adresse'] : null);
        }
        if (array_key_exists('codePostal', $data)) {
            $user->setCodePostal(is_string($data['codePostal']) && $data['codePostal'] !== '' ? $data['codePostal'] : null);
        }
        if (array_key_exists('ville', $data)) {
            $user->setVille(is_string($data['ville']) && $data['ville'] !== '' ? $data['ville'] : null);
        }

        if (array_key_exists('role', $data)) {
            $roleString = is_string($data['role']) && $data['role'] !== '' ? $data['role'] : 'ROLE_USER';
                          
            if ($roleString === 'ROLE_SUPER_ADMIN') {
                return new JsonResponse(
                    ['message' => "Attribution du rôle SUPER_ADMIN interdite via cette interface."],
                    JsonResponse::HTTP_FORBIDDEN
                );
            }

            // on force toujours en tableau, car setRoles attend un array
            $user->setRole($roleString);

}

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($messages, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur mis à jour avec succès.'], JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    /**
     * ADMIN - SUPPRIMER UN UTILISATEUR
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/users/{id}', name: 'api_admin_user_delete', methods: ['DELETE'])]
    public function deleteAdmin(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user || !$user instanceof User) {
            return new JsonResponse(['message' => 'Utilisateur introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($this->getUser()->getRole() === 'ROLE_SUPER_ADMIN' && $user->getRole() !== 'ROLE_USER') {
            return new JsonResponse(['message' => 'Seul un super admin peut supprimer un admin ou un super admin'], JsonResponse::HTTP_FORBIDDEN);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur supprimé avec succès.'], JsonResponse::HTTP_OK);
    }
}
