<?php

namespace App\Controller;

use App\Entity\ProductVariant;
use App\Entity\Product;
use App\Enum\Size;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use App\Repository\ProductRepository;
use App\Repository\ProductVariantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class ProductVariantController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/product-variants', name: 'api_product_variants_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        ProductRepository $productRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $productVariant = new ProductVariant();
        $productVariant->setProduct(isset($data['product']) ? $productRepository->find($data['product']) : null);
        $productVariant->setStock(is_numeric($data['stock'] ?? null) ? (int) $data['stock'] : null);
        $productVariant->setSize(isset($data['size']) ? Size::tryFrom($data['size']) : null);
        $productVariant->setActive(isset($data['active']) ? (bool) $data['active'] : false);

        $errors = $validator->validate($productVariant);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($messages, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($productVariant);
        $em->flush();

        return new JsonResponse(['message' => 'Déclinaison du produit créé avec succès.'], JsonResponse::HTTP_CREATED);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[Route('/product-variants/{id}', name: 'api_product_variants_show', methods: ['GET'])]
    public function show(int $id, ProductVariantRepository $productVariantRepository): JsonResponse
    {
        $productVariant = $productVariantRepository->find($id);
        if (! $productVariant) {
            return new JsonResponse(['message' => 'Déclinaison du produit non trouvé.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = [
            'id'           => $productVariant->getId(),
            'product'      => $productVariant->getProduct()?->getId(),
            'stock'        => $productVariant->getStock(),
            'size'         => $productVariant->getSize()?->value,
            'active'       => $productVariant->isActive()
        ];

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/product-variants/{id}', name: 'api_product_variants_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        ProductVariantRepository $productVariantRepository,
        ProductRepository $productRepository,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $productVariant = $productVariantRepository->find($id);
        if (! $productVariant) {
            return new JsonResponse(['message' => 'Déclinaison du produit introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (array_key_exists('product', $data)) {
            $productVariant->setProduct(is_numeric($data['product']) ? $productRepository->find((int) $data['product']) : null);
        }
        if (array_key_exists('stock', $data)) {
            $productVariant->setStock(is_numeric($data['stock']) ? (int) $data['stock'] : null);
        }
        if (array_key_exists('size', $data)) {
            $productVariant->setSize(is_string($data['size']) && $data['size'] !== '' ? Size::tryFrom($data['size']) : null);
        }
        if (array_key_exists('active', $data)) {
            $productVariant->setActive((bool) $data['active']);
        }

        $errors = $validator->validate($productVariant);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($messages, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Déclinaison du produit mis à jour avec succès.'], JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/product-variants/{id}', name: 'api_product_variants_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        ProductVariantRepository $productVariantRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $productVariant = $productVariantRepository->find($id);
        if (! $productVariant) {
            return new JsonResponse(['message' => 'Déclinaison non trouvée.'], JsonResponse::HTTP_NOT_FOUND);
        }       

         try {
            $em->remove($productVariant);
            $em->flush();
            } catch (ForeignKeyConstraintViolationException) {
                return new JsonResponse(
                    ['message' => 'Cette déclinaison est liée à une ou plusieurs commandes et ne peut pas être supprimée.'],
                    JsonResponse::HTTP_CONFLICT);
        }

        return new JsonResponse(['message' => 'Déclinaison du produit supprimée avec succès.'],JsonResponse::HTTP_OK);
    }
}
