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

    #[Route('/ecurie/{id}', name: 'infraction_ecurie', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function infligerInfractionEcurie(int $id, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $ecurie = $this->ecurieRepository->find($id);
        
        if (!$ecurie) {
            return $this->json(['error' => 'Écurie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['error' => 'JSON invalide: ' . json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }
        
        if (!is_array($data)) {
            return $this->json(['error' => 'Les données doivent être au format JSON objet'], Response::HTTP_BAD_REQUEST);
        }
        
        $requiredFields = ['nom_de_la_course', 'description', 'date'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Le champ '$field' est requis"], Response::HTTP_BAD_REQUEST);
            }
        }

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
            
            if (isset($data['penalite']) && $data['penalite'] !== null) {
                $infraction->setPenalite((int)$data['penalite']);
            }
            
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

    #[Route('/pilote/{id}', name: 'infraction_pilote', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function infligerInfractionPilote(int $id, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $pilote = $this->piloteRepository->find($id);
        
        if (!$pilote) {
            return $this->json(['error' => 'Pilote non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        $requiredFields = ['nom_de_la_course', 'description', 'date'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Le champ '$field' est requis"], Response::HTTP_BAD_REQUEST);
            }
        }

        if (!isset($data['penalite']) && !isset($data['amende'])) {
            return $this->json([
                'error' => 'Au moins une pénalité (points) ou une amende (montant) doit être spécifiée'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $infraction = new RegistreFractions();
            $infraction->setPilote($pilote);
            $infraction->setEcurie($pilote->getEcurie());
            $infraction->setNomDeLaCourse($data['nom_de_la_course']);
            $infraction->setDescription($data['description']);
            $infraction->setDate(new \DateTime($data['date']));
            
            if (isset($data['penalite']) && $data['penalite'] !== null) {
                $infraction->setPenalite((int)$data['penalite']);
            }
            
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

    #[Route('/{type}/{id}', name: 'infraction_generale', methods: ['POST'], requirements: ['type' => 'ecurie|pilote', 'id' => '\d+'])]
    public function infligerInfraction(string $type, int $id, Request $request): JsonResponse
    {
        if ($type === 'ecurie') {
            return $this->infligerInfractionEcurie($id, $request);
        } elseif ($type === 'pilote') {
            return $this->infligerInfractionPilote($id, $request);
        }

        return $this->json(['error' => 'Type invalide. Utilisez "ecurie" ou "pilote"'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('', name: 'infraction_list', methods: ['GET'])]
    public function listerInfractions(Request $request): JsonResponse
    {
        $queryBuilder = $this->entityManager->getRepository(RegistreFractions::class)->createQueryBuilder('i')
            ->leftJoin('i.ecurie', 'e')
            ->leftJoin('i.pilote', 'p')
            ->orderBy('i.date', 'DESC');

        if ($request->query->get('ecurie_id')) {
            $ecurieId = (int)$request->query->get('ecurie_id');
            $queryBuilder->andWhere('e.id = :ecurieId')
                        ->setParameter('ecurieId', $ecurieId);
        }

        if ($request->query->get('pilote_id')) {
            $piloteId = (int)$request->query->get('pilote_id');
            $queryBuilder->andWhere('p.id = :piloteId')
                        ->setParameter('piloteId', $piloteId);
        }

        if ($request->query->get('date')) {
            try {
                $date = new \DateTime($request->query->get('date'));
                $queryBuilder->andWhere('DATE(i.date) = DATE(:date)')
                            ->setParameter('date', $date->format('Y-m-d'));
            } catch (\Exception $e) {
                return $this->json(['error' => 'Format de date invalide. Utilisez YYYY-MM-DD'], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->query->get('date_debut')) {
            try {
                $dateDebut = new \DateTime($request->query->get('date_debut'));
                $queryBuilder->andWhere('i.date >= :dateDebut')
                            ->setParameter('dateDebut', $dateDebut);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Format de date_debut invalide. Utilisez YYYY-MM-DD'], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->query->get('date_fin')) {
            try {
                $dateFin = new \DateTime($request->query->get('date_fin'));
                $dateFin->setTime(23, 59, 59);
                $queryBuilder->andWhere('i.date <= :dateFin')
                            ->setParameter('dateFin', $dateFin);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Format de date_fin invalide. Utilisez YYYY-MM-DD'], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->query->get('course')) {
            $course = $request->query->get('course');
            $queryBuilder->andWhere('i.nomDeLaCourse LIKE :course')
                        ->setParameter('course', '%' . $course . '%');
        }

        if ($request->query->get('type')) {
            $type = $request->query->get('type');
            switch ($type) {
                case 'amende':
                    $queryBuilder->andWhere('i.amende IS NOT NULL');
                    break;
                case 'penalite':
                    $queryBuilder->andWhere('i.penalite IS NOT NULL');
                    break;
                case 'mixte':
                    $queryBuilder->andWhere('i.amende IS NOT NULL AND i.penalite IS NOT NULL');
                    break;
                default:
                    return $this->json(['error' => 'Type invalide. Utilisez: amende, penalite, mixte'], Response::HTTP_BAD_REQUEST);
            }
        }

        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
        $offset = ($page - 1) * $limit;

        $totalQueryBuilder = clone $queryBuilder;
        $total = $totalQueryBuilder->select('COUNT(i.id)')->getQuery()->getSingleScalarResult();

        $infractions = $queryBuilder->setFirstResult($offset)
                                  ->setMaxResults($limit)
                                  ->getQuery()
                                  ->getResult();

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

        return $this->json([
            'infractions' => $result,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int)$total,
                'total_pages' => ceil($total / $limit)
            ],
            'filtres_appliques' => [
                'ecurie_id' => $request->query->get('ecurie_id'),
                'pilote_id' => $request->query->get('pilote_id'),
                'date' => $request->query->get('date'),
                'date_debut' => $request->query->get('date_debut'),
                'date_fin' => $request->query->get('date_fin'),
                'course' => $request->query->get('course'),
                'type' => $request->query->get('type')
            ]
        ]);
    }

    #[Route('/ecurie/{id}/historique', name: 'infraction_ecurie_historique', methods: ['GET'], requirements: ['id' => '\d+'])]
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

    #[Route('/pilote/{id}/historique', name: 'infraction_pilote_historique', methods: ['GET'], requirements: ['id' => '\d+'])]
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

    #[Route('/stats', name: 'infraction_stats', methods: ['GET'])]
    public function getStatistiques(): JsonResponse
    {
        $infractions = $this->entityManager->getRepository(RegistreFractions::class)->findAll();
        
        $totalInfractions = count($infractions);
        $totalAmendes = 0;
        $totalPenalites = 0;
        $infractionsAvecAmende = 0;
        $infractionsAvecPenalite = 0;
        $infractionsAmendeEtPenalite = 0;
        
        $ecuries = [];
        $pilotes = [];
        $courses = [];
        $annees = [];
        
        foreach ($infractions as $infraction) {
            if ($infraction->getAmende()) {
                $totalAmendes += (float)$infraction->getAmende();
                $infractionsAvecAmende++;
            }
            if ($infraction->getPenalite()) {
                $totalPenalites += $infraction->getPenalite();
                $infractionsAvecPenalite++;
            }
            if ($infraction->getAmende() && $infraction->getPenalite()) {
                $infractionsAmendeEtPenalite++;
            }
            
            $ecurie = $infraction->getEcurie();
            if ($ecurie && !isset($ecuries[$ecurie->getId()])) {
                $ecuries[$ecurie->getId()] = [
                    'id' => $ecurie->getId(),
                    'nom' => $ecurie->getNom()
                ];
            }
            
            $pilote = $infraction->getPilote();
            if ($pilote && !isset($pilotes[$pilote->getId()])) {
                $pilotes[$pilote->getId()] = [
                    'id' => $pilote->getId(),
                    'prenom' => $pilote->getPrenom(),
                    'nom' => $pilote->getNom()
                ];
            }
            
            $course = $infraction->getNomDeLaCourse();
            if ($course && !in_array($course, $courses)) {
                $courses[] = $course;
            }
            
            $annee = $infraction->getDate()->format('Y');
            if (!in_array($annee, $annees)) {
                $annees[] = $annee;
            }
        }
        
        return $this->json([
            'statistiques' => [
                'total_infractions' => $totalInfractions,
                'total_amendes' => number_format($totalAmendes, 2),
                'total_penalites' => $totalPenalites,
                'infractions_avec_amende' => $infractionsAvecAmende,
                'infractions_avec_penalite' => $infractionsAvecPenalite,
                'infractions_amende_et_penalite' => $infractionsAmendeEtPenalite,
                'moyenne_amende' => $infractionsAvecAmende > 0 ? number_format($totalAmendes / $infractionsAvecAmende, 2) : 0,
                'moyenne_penalite' => $infractionsAvecPenalite > 0 ? number_format($totalPenalites / $infractionsAvecPenalite, 2) : 0
            ],
            'filtres_disponibles' => [
                'ecuries' => array_values($ecuries),
                'pilotes' => array_values($pilotes),
                'courses' => $courses,
                'annees' => $annees,
                'types' => ['amende', 'penalite', 'mixte']
            ],
            'exemples_filtres' => [
                'par_ecurie' => '/api/infraction?ecurie_id=1',
                'par_pilote' => '/api/infraction?pilote_id=1',
                'par_date' => '/api/infraction?date=2024-05-26',
                'par_periode' => '/api/infraction?date_debut=2024-01-01&date_fin=2024-12-31',
                'par_course' => '/api/infraction?course=Monaco',
                'par_type' => '/api/infraction?type=amende',
                'combinaison' => '/api/infraction?ecurie_id=1&type=penalite&date_debut=2024-01-01'
            ]
        ]);
    }
}