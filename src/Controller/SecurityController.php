<?php

// src/Controller/SecurityController.php
namespace App\Controller;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController
{
    #[Route('/logout', name: '_logout_main', methods: ['POST'])]
    public function someAction(Security $security): Response
    {
        // logout the user in on the current firewall
        $response = $security->logout();

        // you can also disable the csrf logout
        $response = $security->logout(false);

        return $response;

        // return $this->json(['message' => 'Déconnexion réussie']);

        // ... return $response (if set) or e.g. redirect to the homepage
    }
}