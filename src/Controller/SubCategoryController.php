<?php

namespace App\Controller;

use App\Entity\SubCategory;
use App\Repository\SubCategoryRepository;
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
class SubCategoryController extends AbstractController
{
    #[Route('/subcategories', name: 'api_subcategories_index', methods: ['GET'])]
    public function index(SubCategoryRepository $subCategoryRepository): JsonResponse
    {
        $subCategories = $subCategoryRepository->findAll();

        $data = array_map(function (SubCategory $subCategory) {
            return [
                'id'       => $subCategory->getId(),
                'name'     => $subCategory->getName(),
                'slug'     => $subCategory->getSlug(),
            ];
        }, $subCategories);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[Route('/subcategories/grouped', name: 'api_subcategories_grouped', methods: ['GET'])]
    public function grouped(SubCategoryRepository $subCategoryRepository): JsonResponse
    {
        $rows = $subCategoryRepository->findGroupedByCategory();

        $grouped = [];
        foreach ($rows as $r) {
            $key = $r['categorySlug'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'categoryId'     => (int) $r['categoryId'],
                    'categoryName'   => $r['categoryName'],
                    'categorySlug'   => $r['categorySlug'],
                    'subCategories'  => [],
                ];
            }
            $grouped[$key]['subCategories'][] = [
                'subCategoryId'   => (int) $r['id'],
                'subCategoryName' => $r['name'],
                'subCategorySlug' => $r['slug'],
            ];
        }
        $grouped = array_values($grouped);
        return new JsonResponse($grouped, JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/subcategories', name: 'api_subcategories_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        CategoryRepository $categoryRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $subCategory = new SubCategory();
        $subCategory->setName(is_string($data['name'] ?? null) ? $data['name'] : null);
        $subCategory->setCategory(isset($data['category']) ? $categoryRepository->find($data['category']) : null);

        $errors = $validator->validate($subCategory);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($messages, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($subCategory);
        $em->flush();

        return new JsonResponse(['message' => 'Sous-catégorie créée avec succès.'], JsonResponse::HTTP_CREATED);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[Route('/subcategories/{id}', name: 'api_subcategories_show', methods: ['GET'])]
    public function show(int $id, SubCategoryRepository $subCategoryRepository): JsonResponse
    {
        $subCategory = $subCategoryRepository->find($id);

        if (!$subCategory) {
            return new JsonResponse(['message' => 'Sous-catégorie non trouvée.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id'       => $subCategory->getId(),
            'name'     => $subCategory->getName(),
            'slug'     => $subCategory->getSlug(),
        ], JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/subcategories/{id}', name: 'api_subcategories_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        SubCategoryRepository $subCategoryRepository,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $subCategory = $subCategoryRepository->find($id);
        if (!$subCategory) {
            return new JsonResponse(['message' => 'Sous-catégorie introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (array_key_exists('name', $data)) {
            $subCategory->setName(is_string($data['name']) ? $data['name'] : null);
        }

        $errors = $validator->validate($subCategory);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($messages, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Sous-catégorie modifiée avec succès.'], JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/subcategories/{id}', name: 'api_subcategories_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        SubCategoryRepository $subCategoryRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $subCategory = $subCategoryRepository->find($id);
        if (!$subCategory) {
            return new JsonResponse(['message' => 'Sous-catégorie non trouvée.'], JsonResponse::HTTP_NOT_FOUND);
        }

        try {
            $em->remove($subCategory);
            $em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return new JsonResponse(['message' => 'Cette sous-catégorie est liée à un ou plusieurs produits et ne peut pas être supprimée.'], JsonResponse::HTTP_CONFLICT);
        }

        return new JsonResponse(['message' => 'Sous-catégorie supprimée avec succès.'], JsonResponse::HTTP_OK);
    }
}
