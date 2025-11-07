<?php

namespace App\Controller;

use App\Entity\Ecurie;
use App\Entity\Pilote;
use App\Entity\RegistreFractions;
use App\Repository\EcurieRepository;
use App\Repository\PiloteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/infraction')]
class InfractionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EcurieRepository $ecurieRepository,
        private PiloteRepository $piloteRepository
    ) {}

    /**
     * Infliger une amende/pénalité à une écurie
     */
    #[Route('/ecurie/{id}', name: 'infraction_ecurie', methods: ['POST'])]
    public function infligerInfractionEcurie(int $id, Request $request): JsonResponse
    {
        $ecurie = $this->ecurieRepository->find($id);
        
        if (!$ecurie) {
            return $this->json(['error' => 'Écurie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        // Validation des données requises
        $requiredFields = ['nom_de_la_course', 'description', 'date'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Le champ '$field' est requis"], Response::HTTP_BAD_REQUEST);
            }
        }

        // Vérifier qu'au moins une pénalité ou une amende est spécifiée
        if (!isset($data['penalite']) && !isset($data['amende'])) {
            return $this->json([
                'error' => 'Au moins une pénalité (points) ou une amende (montant) doit être spécifiée'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $infraction = new RegistreFractions();
            $infraction->setEcurie($ecurie);
            $infraction->setNomDeLaCourse($data['nom_de_la_course']);
            $infraction->setDescription($data['description']);
            $infraction->setDate(new \DateTime($data['date']));
            
            // Pénalité (points) - optionnelle
            if (isset($data['penalite']) && $data['penalite'] !== null) {
                $infraction->setPenalite((int)$data['penalite']);
            }
            
            // Amende (montant) - optionnelle
            if (isset($data['amende']) && $data['amende'] !== null) {
                $infraction->setAmende((string)$data['amende']);
            }

            $this->entityManager->persist($infraction);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Infraction enregistrée pour l\'écurie avec succès',
                'infraction_id' => $infraction->getId(),
                'ecurie' => $ecurie->getNom(),
                'penalite' => $infraction->getPenalite(),
                'amende' => $infraction->getAmende()
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Infliger une amende/pénalité à un pilote
     */
    #[Route('/pilote/{id}', name: 'infraction_pilote', methods: ['POST'])]
    public function infligerInfractionPilote(int $id, Request $request): JsonResponse
    {
        $pilote = $this->piloteRepository->find($id);
        
        if (!$pilote) {
            return $this->json(['error' => 'Pilote non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        // Validation des données requises
        $requiredFields = ['nom_de_la_course', 'description', 'date'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Le champ '$field' est requis"], Response::HTTP_BAD_REQUEST);
            }
        }

        // Vérifier qu'au moins une pénalité ou une amende est spécifiée
        if (!isset($data['penalite']) && !isset($data['amende'])) {
            return $this->json([
                'error' => 'Au moins une pénalité (points) ou une amende (montant) doit être spécifiée'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $infraction = new RegistreFractions();
            $infraction->setPilote($pilote);
            $infraction->setEcurie($pilote->getEcurie()); // Associer aussi l'écurie du pilote
            $infraction->setNomDeLaCourse($data['nom_de_la_course']);
            $infraction->setDescription($data['description']);
            $infraction->setDate(new \DateTime($data['date']));
            
            // Pénalité (points) - optionnelle
            if (isset($data['penalite']) && $data['penalite'] !== null) {
                $infraction->setPenalite((int)$data['penalite']);
            }
            
            // Amende (montant) - optionnelle
            if (isset($data['amende']) && $data['amende'] !== null) {
                $infraction->setAmende((string)$data['amende']);
            }

            $this->entityManager->persist($infraction);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Infraction enregistrée pour le pilote avec succès',
                'infraction_id' => $infraction->getId(),
                'pilote' => $pilote->getPrenom() . ' ' . $pilote->getNom(),
                'ecurie' => $pilote->getEcurie()->getNom(),
                'penalite' => $infraction->getPenalite(),
                'amende' => $infraction->getAmende()
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Infliger une amende/pénalité (route générale avec type)
     */
    #[Route('/{type}/{id}', name: 'infraction_generale', methods: ['POST'], requirements: ['type' => 'ecurie|pilote'])]
    public function infligerInfraction(string $type, int $id, Request $request): JsonResponse
    {
        if ($type === 'ecurie') {
            return $this->infligerInfractionEcurie($id, $request);
        } elseif ($type === 'pilote') {
            return $this->infligerInfractionPilote($id, $request);
        }

        return $this->json(['error' => 'Type invalide. Utilisez "ecurie" ou "pilote"'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Lister toutes les infractions
     */
    #[Route('', name: 'infraction_list', methods: ['GET'])]
    public function listerInfractions(): JsonResponse
    {
        $infractions = $this->entityManager->getRepository(RegistreFractions::class)->findAll();
        $result = [];

        foreach ($infractions as $infraction) {
            $result[] = [
                'id' => $infraction->getId(),
                'nom_de_la_course' => $infraction->getNomDeLaCourse(),
                'description' => $infraction->getDescription(),
                'date' => $infraction->getDate()->format('Y-m-d'),
                'penalite' => $infraction->getPenalite(),
                'amende' => $infraction->getAmende(),
                'ecurie' => [
                    'id' => $infraction->getEcurie()->getId(),
                    'nom' => $infraction->getEcurie()->getNom()
                ],
                'pilote' => $infraction->getPilote() ? [
                    'id' => $infraction->getPilote()->getId(),
                    'prenom' => $infraction->getPilote()->getPrenom(),
                    'nom' => $infraction->getPilote()->getNom()
                ] : null
            ];
        }

        return $this->json($result);
    }

    /**
     * Lister les infractions d'une écurie
     */
    #[Route('/ecurie/{id}/historique', name: 'infraction_ecurie_historique', methods: ['GET'])]
    public function historiqueInfractionsEcurie(int $id): JsonResponse
    {
        $ecurie = $this->ecurieRepository->find($id);
        
        if (!$ecurie) {
            return $this->json(['error' => 'Écurie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $infractions = [];
        foreach ($ecurie->getRegistreInfractions() as $infraction) {
            $infractions[] = [
                'id' => $infraction->getId(),
                'nom_de_la_course' => $infraction->getNomDeLaCourse(),
                'description' => $infraction->getDescription(),
                'date' => $infraction->getDate()->format('Y-m-d'),
                'penalite' => $infraction->getPenalite(),
                'amende' => $infraction->getAmende(),
                'pilote_implique' => $infraction->getPilote() ? [
                    'id' => $infraction->getPilote()->getId(),
                    'prenom' => $infraction->getPilote()->getPrenom(),
                    'nom' => $infraction->getPilote()->getNom()
                ] : null
            ];
        }

        return $this->json([
            'ecurie' => [
                'id' => $ecurie->getId(),
                'nom' => $ecurie->getNom()
            ],
            'infractions' => $infractions,
            'total_infractions' => count($infractions)
        ]);
    }

    /**
     * Lister les infractions d'un pilote
     */
    #[Route('/pilote/{id}/historique', name: 'infraction_pilote_historique', methods: ['GET'])]
    public function historiqueInfractionsPilote(int $id): JsonResponse
    {
        $pilote = $this->piloteRepository->find($id);
        
        if (!$pilote) {
            return $this->json(['error' => 'Pilote non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $infractions = [];
        foreach ($pilote->getRegistreInfractions() as $infraction) {
            $infractions[] = [
                'id' => $infraction->getId(),
                'nom_de_la_course' => $infraction->getNomDeLaCourse(),
                'description' => $infraction->getDescription(),
                'date' => $infraction->getDate()->format('Y-m-d'),
                'penalite' => $infraction->getPenalite(),
                'amende' => $infraction->getAmende()
            ];
        }

        return $this->json([
            'pilote' => [
                'id' => $pilote->getId(),
                'prenom' => $pilote->getPrenom(),
                'nom' => $pilote->getNom(),
                'ecurie' => $pilote->getEcurie()->getNom()
            ],
            'infractions' => $infractions,
            'total_infractions' => count($infractions)
        ]);
    }
}