<?php
// src/Controller/UserController.php
namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[Route('/create', name: 'create_user', methods: ['POST'])]
    public function createUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        if(!isset($content['email']) || !isset($content['password'])) {
            return new JsonResponse('champ email ou mot de passe manquant', JsonResponse::HTTP_BAD_REQUEST);
        }
        $user = new User();
        $user->setEmail($content['email']);

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $content['password']
        );
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);

        $entityManager->flush();

        return new JsonResponse('Saved new User with id '. $user->getId());
    }
}