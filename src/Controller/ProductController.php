<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Enum\Genre;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class ProductController extends AbstractController
{
    #[Route('/products', name: 'api_product_show', methods: ['GET'])]
    public function show(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAllActive();
        
        if (!$products) {
            return new JsonResponse(['message' => 'Produits non trouvés.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = array_map(function (Product $product) {

            return [
                'id'               => $product->getId(),
                'name'             => $product->getName(),
                'category'         => $product->getSubCategory()?->getCategory()?->getName(),
                'categorySlug'     => $product->getSubCategory()?->getCategory()?->getSlug(),
                'subCategory'      => $product->getSubCategory()?->getName(),
                'subCategorySlug'  => $product->getSubCategory()?->getSlug(),
                'genre'            => $product->getGenre()?->label(),
                'description'      => $product->getDescription(),
                'price'            => $product->getPrice() !== null ? number_format((float)$product->getPrice(), 2, ',', '') : null,
                'date'             => $product->getDate()?->format('d-m-Y'),
                'imageUrl'         => $this->getParameter('app.url') . '/images/products/' . $product->getImageUrl()
            ];
        }, $products);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[Route('/products/category/{slug}', name: 'api_products_by_category', methods: ['GET'])]
    public function byCategory(string $slug, ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findByCategory($slug);
        
        if (!$products) {
            return new JsonResponse(['message' => 'Produits non trouvés.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = array_map(function (Product $product) {

            return [
                'id'               => $product->getId(),
                'name'             => $product->getName(),
                'category'         => $product->getSubCategory()?->getCategory()?->getName(),
                'categorySlug'     => $product->getSubCategory()?->getCategory()?->getSlug(),
                'subCategory'      => $product->getSubCategory()?->getName(),
                'subCategorySlug'  => $product->getSubCategory()?->getSlug(),
                'genre'            => $product->getGenre()?->label(),
                'description'      => $product->getDescription(),
                'price'            => $product->getPrice() !== null ? number_format((float)$product->getPrice(), 2, ',', '') : null,
                'date'             => $product->getDate()?->format('d-m-Y'),
                'imageUrl'         => $this->getParameter('app.url') . '/images/products/' . $product->getImageUrl()
            ];
        }, $products);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[Route('/products/subcategory/{slug}', name: 'api_products_by_subcategory', methods: ['GET'])]
    public function bySubCategory(string $slug, ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findBySubCategory($slug);
        
        if (!$products) {
            return new JsonResponse(['message' => 'Produits non trouvés.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = array_map(function (Product $product) {

            return [
                'id'               => $product->getId(),
                'name'             => $product->getName(),
                'category'         => $product->getSubCategory()?->getCategory()?->getName(),
                'categorySlug'     => $product->getSubCategory()?->getCategory()?->getSlug(),
                'subCategory'      => $product->getSubCategory()?->getName(),
                'subCategorySlug'  => $product->getSubCategory()?->getSlug(),
                'genre'            => $product->getGenre()?->label(),
                'description'      => $product->getDescription(),
                'price'            => $product->getPrice() !== null ? number_format((float)$product->getPrice(), 2, ',', '') : null,
                'date'             => $product->getDate()?->format('d-m-Y'),
                'imageUrl'         => $this->getParameter('app.url') . '/images/products/' . $product->getImageUrl()
            ];
        }, $products);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[Route('/products/latest', name: 'api_products_latest', methods: ['GET'])]
    public function latest(ProductRepository $productRepository): JsonResponse
    {
       $products = $productRepository->findLatestProducts();
        
        if (!$products) {
            return new JsonResponse(['message' => 'Produits non trouvés.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = array_map(function (Product $product) {

            return [
                'id'               => $product->getId(),
                'name'             => $product->getName(),
                'category'         => $product->getSubCategory()?->getCategory()?->getName(),
                'categorySlug'     => $product->getSubCategory()?->getCategory()?->getSlug(),
                'subCategory'      => $product->getSubCategory()?->getName(),
                'subCategorySlug'  => $product->getSubCategory()?->getSlug(),
                'genre'            => $product->getGenre()?->label(),
                'description'      => $product->getDescription(),
                'price'            => $product->getPrice() !== null ? number_format((float)$product->getPrice(), 2, ',', '') : null,
                'date'             => $product->getDate()?->format('d-m-Y'),
                'imageUrl'         => $this->getParameter('app.url') . '/images/products/' . $product->getImageUrl()
            ];
        }, $products);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/products', name: 'api_product_show_admin', methods: ['GET'])]
    public function showAdmin(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAllAdmin();
        
        if (!$products) {
            return new JsonResponse(['message' => 'Produits non trouvés.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = array_map(function (Product $product) {

            return [
                'id'               => $product->getId(),
                'name'             => $product->getName(),
                'category'         => $product->getSubCategory()?->getCategory()?->getName(),
                'categorySlug'     => $product->getSubCategory()?->getCategory()?->getSlug(),
                'subCategory'      => $product->getSubCategory()?->getName(),
                'subCategorySlug'  => $product->getSubCategory()?->getSlug(),
                'genre'            => $product->getGenre()?->label(),
                'description'      => $product->getDescription(),
                'price'            => $product->getPrice() !== null ? number_format((float)$product->getPrice(), 2, ',', '') : null,
                'date'             => $product->getDate()?->format('d-m-Y'),
                'imageUrl'         => $this->getParameter('app.url') . '/images/products/' . $product->getImageUrl()
            ];
        }, $products);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/products', name: 'api_product_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        SubCategoryRepository $subCategoryRepository
    ): JsonResponse {

        $data = $request->request->all();
        $file = $request->files->get('imageFile');

        $product = new Product();
        $product->setName(is_string($data['name'] ?? null) ? $data['name'] : null);
        $product->setSubCategory(isset($data['subCategory']) ? $subCategoryRepository->find($data['subCategory']) : null);
        $product->setGenre(isset($data['genre']) ? Genre::tryFrom($data['genre']) : null);
        $product->setDescription($data['description'] ?? null);
        $rawPrice = $data['price'] ?? null;
        $product->setPrice($rawPrice !== null && is_numeric(str_replace(',', '.', $rawPrice)) ? (string) str_replace(',', '.', $rawPrice) : null);

        if ($file && !$file->isValid()) {
            $errorCode = $file->getError();
            if (\in_array($errorCode, [\UPLOAD_ERR_INI_SIZE, \UPLOAD_ERR_FORM_SIZE], true)) {
                return new JsonResponse(['message' => 'Le fichier dépasse la taille maximale autorisée.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        if ($file instanceof UploadedFile) {
            $product->setImageFile($file);
        }
        
        $errors = $validator->validate($product, null, ['create']);

        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($messages, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($product);
        $em->flush();

        return new JsonResponse(['message' => 'Produit créé avec succès.'], JsonResponse::HTTP_CREATED);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[Route('/products/{id}', name: 'api_product_show_id', methods: ['GET'])]
    public function showIdWithVariants(int $id, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->findByIdWithVariants($id);
        if (!$product) {
            return new JsonResponse(['message' => 'Produit non trouvé.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $variants = array_map(fn(ProductVariant $v) => [
            'id'       => $v->getId(),
            'size'     => $v->getSize()?->value,
            'stock'    => $v->getStock(),
            'isActive' => $v->isActive(),
        ], $product->getVariants()->toArray());

        $data = [
            'id'               => $product->getId(),
            'name'             => $product->getName(),
            'category'         => $product->getSubCategory()?->getCategory()?->getName(),
            'categorySlug'     => $product->getSubCategory()?->getCategory()?->getSlug(),
            'subCategory'      => $product->getSubCategory()?->getName(),
            'subCategoryId'    => $product->getSubCategory()?->getId(),
            'subCategorySlug'  => $product->getSubCategory()?->getSlug(),
            'genre'            => $product->getGenre()?->label(),
            'description'      => $product->getDescription(),
            'price'            => $product->getPrice() !== null ? number_format((float) $product->getPrice(), 2, ',', '') : null,
            'date'             => $product->getDate()?->format('d-m-Y'),
            'imageUrl'         => $this->getParameter('app.url') . '/images/products/' . $product->getImageUrl(),
            'variants'         => $variants,
        ];

        return $this->json($data);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/products/{id}', name: 'api_product_update', methods: ['POST'])]
    public function update(
        int $id,
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        SubCategoryRepository $subCategoryRepository,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {

        $product = $productRepository->find($id);
        if (!$product) {
            return new JsonResponse(['message' => 'Produit introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = $request->request->all();
        $file = $request->files->get('imageFile');

        if (array_key_exists('name', $data)) { $product->setName(is_string($data['name']) ? $data['name'] : null); }
        if (array_key_exists('subCategory', $data)) {$product->setSubCategory(is_numeric($data['subCategory']) ? $subCategoryRepository->find((int) $data['subCategory']) : null);}
        if (array_key_exists('genre', $data)) { $product->setGenre(is_string($data['genre']) && $data['genre'] !== '' ? Genre::tryFrom($data['genre']) : null); }
        if (array_key_exists('description', $data)) { $product->setDescription(is_string($data['description']) ? $data['description'] : null); }
        if (array_key_exists('price', $data)) { $rawPrice = $data['price'] ?? null; $product->setPrice($rawPrice !== null && is_numeric(str_replace(',', '.', $rawPrice)) ? (string) str_replace(',', '.', $rawPrice) : null); }

        if ($file && !$file->isValid()) {
            $errorCode = $file->getError();
            if (\in_array($errorCode, [\UPLOAD_ERR_INI_SIZE, \UPLOAD_ERR_FORM_SIZE], true)) {
                return new JsonResponse(['message' => 'Le fichier dépasse la taille maximale autorisée.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        if ($file instanceof UploadedFile) {
            $product->setImageFile($file);
        }

        $errors = $validator->validate($product, null, ['update']);

        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($messages, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Produit mis à jour avec succès.'], JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/products/{id}', name: 'api_product_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        ProductRepository $productRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $product = $productRepository->find($id);
        if (!$product) {
            return new JsonResponse(['error' => 'Produit non trouvé.'], JsonResponse::HTTP_NOT_FOUND);
        }

         try {
            $em->remove($product);
            $em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            return new JsonResponse(['message' => 'Ce produit est liée à une ou plusieurs variantes de produits et ne peut-être supprimé'],JsonResponse::HTTP_CONFLICT);
        }

        return new JsonResponse(['message' => 'Produit supprimé avec succès.'], JsonResponse::HTTP_OK);
    }
}