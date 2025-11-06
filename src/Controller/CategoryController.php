<?php

// src/Controller/CategoryController.php
namespace App\Controller;

// ...
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/category')]
class CategoryController extends AbstractController
{
    #[Route('/create', name: 'create_category', methods: ['POST'])]
    public function createCategory(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $content = json_decode($request->getContent(), true);

        $category = new Category();
        if(!isset($content['name'])) {
            return new JsonResponse('champ name manquant', JsonResponse::HTTP_BAD_REQUEST);
        }
        $category->setName($content['name']);

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($category);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new JsonResponse('Saved new category with id '.$category->getId());
    }
}