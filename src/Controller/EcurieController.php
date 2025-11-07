<?php

namespace App\Controller;

use App\Entity\Ecurie;
use App\Entity\Pilote;
use App\Repository\EcurieRepository;
use App\Repository\PiloteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ecurie')]
class EcurieController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EcurieRepository $ecurieRepository,
        private PiloteRepository $piloteRepository
    ) {}

    /**
     * Récupère une écurie avec ses pilotes
     */
    #[Route('/{id}', name: 'ecurie_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $ecurie = $this->ecurieRepository->find($id);
        
        if (!$ecurie) {
            return $this->json(['error' => 'Écurie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $pilotes = [];
        foreach ($ecurie->getPilote() as $pilote) {
            $pilotes[] = [
                'id' => $pilote->getId(),
                'prenom' => $pilote->getPrenom(),
                'nom' => $pilote->getNom(),
                'role' => $pilote->getRole(),
                'points_license' => $pilote->getPointsLicense(),
                'date' => $pilote->getDate()->format('Y-m-d')
            ];
        }

        return $this->json([
            'id' => $ecurie->getId(),
            'nom' => $ecurie->getNom(),
            'moteur' => $ecurie->getMoteur(),
            'pilotes' => $pilotes
        ]);
    }

    /**
     * Modifie les pilotes d'une écurie
     */
    #[Route('/{id}/pilotes', name: 'ecurie_update_pilotes', methods: ['PUT', 'PATCH'])]
    public function updatePilotes(int $id, Request $request): JsonResponse
    {
        $ecurie = $this->ecurieRepository->find($id);
        
        if (!$ecurie) {
            return $this->json(['error' => 'Écurie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['pilotes']) || !is_array($data['pilotes'])) {
            return $this->json(['error' => 'Format de données invalide. "pilotes" requis.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Traiter chaque pilote dans la requête
            foreach ($data['pilotes'] as $piloteData) {
                if (isset($piloteData['id'])) {
                    // Modifier un pilote existant
                    $pilote = $this->piloteRepository->find($piloteData['id']);
                    if ($pilote && $pilote->getEcurie()->getId() === $ecurie->getId()) {
                        $this->updatePiloteData($pilote, $piloteData);
                    }
                } else {
                    // Créer un nouveau pilote
                    $pilote = new Pilote();
                    $pilote->setEcurie($ecurie);
                    $this->updatePiloteData($pilote, $piloteData);
                    $this->entityManager->persist($pilote);
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Pilotes mis à jour avec succès',
                'ecurie_id' => $ecurie->getId()
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Ajoute un nouveau pilote à une écurie
     */
    #[Route('/{id}/pilotes/add', name: 'ecurie_add_pilote', methods: ['POST'])]
    public function addPilote(int $id, Request $request): JsonResponse
    {
        $ecurie = $this->ecurieRepository->find($id);
        
        if (!$ecurie) {
            return $this->json(['error' => 'Écurie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        $requiredFields = ['prenom', 'nom', 'role', 'points_license', 'date'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Le champ '$field' est requis"], Response::HTTP_BAD_REQUEST);
            }
        }

        try {
            $pilote = new Pilote();
            $pilote->setEcurie($ecurie);
            $this->updatePiloteData($pilote, $data);
            
            $this->entityManager->persist($pilote);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Pilote ajouté avec succès',
                'pilote_id' => $pilote->getId()
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de l\'ajout: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Supprime un pilote d'une écurie
     */
    #[Route('/{ecurieId}/pilotes/{piloteId}', name: 'ecurie_remove_pilote', methods: ['DELETE'])]
    public function removePilote(int $ecurieId, int $piloteId): JsonResponse
    {
        $ecurie = $this->ecurieRepository->find($ecurieId);
        $pilote = $this->piloteRepository->find($piloteId);
        
        if (!$ecurie) {
            return $this->json(['error' => 'Écurie non trouvée'], Response::HTTP_NOT_FOUND);
        }
        
        if (!$pilote) {
            return $this->json(['error' => 'Pilote non trouvé'], Response::HTTP_NOT_FOUND);
        }
        
        if ($pilote->getEcurie()->getId() !== $ecurie->getId()) {
            return $this->json(['error' => 'Ce pilote n\'appartient pas à cette écurie'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->entityManager->remove($pilote);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Pilote supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste toutes les écuries avec leurs pilotes
     */
    #[Route('', name: 'ecurie_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $ecuries = $this->ecurieRepository->findAll();
        $result = [];

        foreach ($ecuries as $ecurie) {
            $pilotes = [];
            foreach ($ecurie->getPilote() as $pilote) {
                $pilotes[] = [
                    'id' => $pilote->getId(),
                    'prenom' => $pilote->getPrenom(),
                    'nom' => $pilote->getNom(),
                    'role' => $pilote->getRole(),
                    'points_license' => $pilote->getPointsLicense()
                ];
            }

            $result[] = [
                'id' => $ecurie->getId(),
                'nom' => $ecurie->getNom(),
                'moteur' => $ecurie->getMoteur(),
                'pilotes' => $pilotes
            ];
        }

        return $this->json($result);
    }

    /**
     * Méthode helper pour mettre à jour les données d'un pilote
     */
    private function updatePiloteData(Pilote $pilote, array $data): void
    {
        if (isset($data['prenom'])) {
            $pilote->setPrenom($data['prenom']);
        }
        if (isset($data['nom'])) {
            $pilote->setNom($data['nom']);
        }
        if (isset($data['role'])) {
            $pilote->setRole($data['role']);
        }
        if (isset($data['points_license'])) {
            $pilote->setPointsLicense($data['points_license']);
        }
        if (isset($data['date'])) {
            if (is_string($data['date'])) {
                $pilote->setDate(new \DateTime($data['date']));
            }
        }
    }
}