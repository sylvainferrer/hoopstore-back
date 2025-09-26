<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'api_categories_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();

        $data = array_map(function (Category $category) {
            return [
                'id'   => $category->getId(),
                'name' => $category->getName(),
            ];
        }, $categories);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
       
    /* ------------------------------------------------------------------------------------------------------------ */

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/categories', name: 'api_categories_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        $category = new Category();

        $category->setName(is_string($data['name'] ?? null) ? $data['name'] : null);

        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }

            return new JsonResponse($messages,JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($category);
        $em->flush();

        return new JsonResponse(['message' => 'Catégorie créée avec succès.'], JsonResponse::HTTP_CREATED);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[Route('/categories/{id}', name: 'api_categories_show', methods: ['GET'])]
    public function show(int $id, CategoryRepository $categoryRepository): JsonResponse
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            return new JsonResponse(['message' => 'Catégorie non trouvée.'],JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(
            [
                'id'   => $category->getId(),
                'name' => $category->getName(),
            ],
            JsonResponse::HTTP_OK
        );
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/categories/{id}', name: 'api_categories_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $category = $categoryRepository->find($id);
        if (!$category) {
            return new JsonResponse(['message' => 'Catégorie introuvable.'],JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (array_key_exists('name', $data)) { $category->setName(is_string($data['name']) ? $data['name'] : null); }

        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }

            return new JsonResponse($messages, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Catégorie modifiée avec succès.'], JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/categories/{id}', name: 'api_categories_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $category = $categoryRepository->find($id);
        if (!$category) {
            return new JsonResponse(['message' => 'Catégorie non trouvée.'],JsonResponse::HTTP_NOT_FOUND);
        }

        try {
            $em->remove($category);
            $em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return new JsonResponse(
                ['message' => 'Cette catégorie est liée à une ou plusieurs sous-catégories et ne peut pas être supprimée.'],
                JsonResponse::HTTP_CONFLICT);
        }

        return new JsonResponse(['message' => 'Catégorie supprimée avec succès.'],JsonResponse::HTTP_OK);
    }
}