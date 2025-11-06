<?php

// src/Controller/ProductController.php
namespace App\Controller;

// ...
use App\Entity\Product;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use App\Service\ProductManager;

#[Route('/api/product')]
class ProductController extends AbstractController
{
    #[Route('/create', name: 'create_product', methods: ['POST'])]
    public function createProduct(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $content = json_decode($request->getContent(), true);

        $product = new Product();
        $product->setName($content['name']);
        $product->setUnitPrice($content['unitPrice']);
        $product->setCreatedAt(new \Datetime());
        $product->setDescription($content['description']);

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new product with id '.$product->getId());
    }

    #[Route('/{id}/edit', name: 'edit_product', methods: ['PATCH', 'PUT'])]
    public function editProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        int $id
    ): Response
    {
        $content = json_decode($request->getContent(), true);
        $product = $entityManager->getRepository(Product::class)->find($id);
        if (isset($content['name'])) {
            $product->setName($content['name']);
        }
        $product->setUnitPrice($content['unitPrice']);
        $product->setDescription($content['description']);

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Update product with id '.$product->getId());
    }

    #[Route('/{id}/delete', name: 'delete_product', methods : ['DELETE'])]
    public function deleteProduct(
        EntityManagerInterface $entityManager,
        int $id
    ): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->remove($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Delete product with ID => '.$id);
    }

    #[Route('/all', name: 'all_product', methods: ['GET'])]
    public function allProduct(
        EntityManagerInterface $entityManager,
        ProductManager $productManager
    ): JsonResponse
    {
        $message = $productManager->getHappyMessage();

        dump($message);

        $products = $entityManager->getRepository(Product::class)->findAll();

        return new JsonResponse('All products');
    }

    #[Route('/avalaible/{minPrice}/{maxPrice}', name: 'avalaible_products', methods: ['GET'])]
    public function avalaibleProducts(
        EntityManagerInterface $entityManager,
        string $minPrice,
        string $maxPrice
    ): JsonResponse
    {
        $products = $entityManager->getRepository(Product::class)->getAllProductsInStorageBetweenPrice($minPrice, $maxPrice);

        return new JsonResponse($products, JsonResponse::HTTP_OK);
    }

    #[Route('/createdAt/price/categoryId', name: 'get_products_with_conditions', methods: ['GET'])]
    public function getProductsWithConditions(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        Request $request
    ): JsonResponse
    {
        try {
            $headers = $request->headers->all();

            $products = $entityManager->getRepository(Product::class)->getProductsWithConditions(
                new \Datetime($headers['createdAt'][0]), 
                (float) $headers['price'][0], 
                (int) $headers['categoryid'][0]
            );

            return new JsonResponse($products, JsonResponse::HTTP_OK);
        } catch(\Exception $e) {
            $logger->error("getProductsWithConditions " . $e->getMessage());
            return new JsonResponse(
                ["problème récupération de produits par critères"]
                , JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/category/{categoryId}', name: 'get_products_by_category', methods: ['GET'])]
    public function getProductsByCategory(
        EntityManagerInterface $entityManager,
        int $categoryId
    ): JsonResponse
    {
        // option utilisation des collections d'ojets propres à Symfony

        $category = $entityManager->getRepository(Category::class)->find($categoryId);

        $tab=[];
        foreach($category->getProducts() as $key => $product)
        {
            $tab[$key]['id']    = $product->getId();
            $tab[$key]['name']  = $product->getName();
        }

        return new JsonResponse($tab, JsonResponse::HTTP_OK);

        // option requête sur-mesure via DQL queryBuilder
        $products = $entityManager->getRepository(Product::class)->getAllProductsByCategory($categoryId);

        return new JsonResponse($products, JsonResponse::HTTP_OK);
    }

    #[Route('/test', name: 'test_product', methods: ['GET'])]
    public function getInfosRequest(
        Request $request
    ): Response
    {
        // $param = $request->attributes->get('param');
        $content = json_decode($request->getContent(), true);

        dd($content);

        return new Response('JSON test', Response::HTTP_OK);
    }
}