<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'email' => 'admin@f1.com',
                'password' => 'admin123',
                'roles' => ['ROLE_ADMIN']
            ],
            [
                'email' => 'user@f1.com',
                'password' => 'user123',
                'roles' => ['ROLE_USER']
            ],
            [
                'email' => 'commissaire@f1.com',
                'password' => 'commissaire123',
                'roles' => ['ROLE_USER']
            ]
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setRoles($userData['roles']);
            
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);
            
            $manager->persist($user);
        }

        $manager->flush();
    }
}