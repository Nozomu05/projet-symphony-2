<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Psr\Log\LoggerInterface;

class LuckyController extends AbstractController
{
    #[Route('/api/lucky/number', name: 'random_number')]
    public function number(): Response
    {
        $number = random_int(0, 100);

        return $this->render('lucky/number.html.twig', [
            'number' => $number,
        ]);
    }

    #[Route('/lucky/redirect', 
        name: 'redirect_to_random_number'
    )]
    public function redirectToRandomNumber(LoggerInterface $logger): RedirectResponse
    {
        $logger->info("redirectToRandomNumber => la redirection s'est bien passée");

        return $this->redirectToRoute('marks', [
            'note1' => 20, 
            'note2' => 15,
            'note3' => 10,
            'note4' => 5
        ]);
    }

    #[Route('/notes/eleves/{note1}/{note2}/{note3}/{note4}', 
        name: 'marks', 
        requirements: [
            'note1' => '\d+', 
            'note2' => '\d+', 
            'note3' => '\d+', 
            'note4' => '\d+'
        ]
    )]
    public function getMarks(int $note1, int $note2,int $note3,int $note4): Response
    {
        //$randomNumberPage = $this->generateUrl('random_number');

        /*$note1 = random_int(0, 20);
        $note2 = random_int(0, 20);
        $note3 = random_int(0, 20);
        $note4 = random_int(0, 20);*/

        return $this->render('notes/eleves.html.twig', [
            'note1' => $note1,
            'note2' => $note2,
            'note3' => $note3,
            'note4' => $note4,
        ]);
    }
}