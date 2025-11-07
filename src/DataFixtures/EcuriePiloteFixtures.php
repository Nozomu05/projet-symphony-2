<?php

namespace App\DataFixtures;

use App\Entity\Ecurie;
use App\Entity\Pilote;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EcuriePiloteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $ecuries = [
            [
                'nom' => 'Red Bull Racing',
                'moteur' => 'Honda RBPT',
                'pilotes' => [
                    ['prenom' => 'Max', 'nom' => 'Verstappen', 'points_license' => 12, 'date' => '1997-09-30', 'role' => 'Pilote titulaire'],
                    ['prenom' => 'Sergio', 'nom' => 'Perez', 'points_license' => 8, 'date' => '1990-01-26', 'role' => 'Pilote titulaire'],
                    ['prenom' => 'Liam', 'nom' => 'Lawson', 'points_license' => 0, 'date' => '2002-02-11', 'role' => 'Pilote de réserve']
                ]
            ],
            [
                'nom' => 'Mercedes-AMG F1',
                'moteur' => 'Mercedes',
                'pilotes' => [
                    ['prenom' => 'Lewis', 'nom' => 'Hamilton', 'points_license' => 6, 'date' => '1985-01-07', 'role' => 'Pilote titulaire'],
                    ['prenom' => 'George', 'nom' => 'Russell', 'points_license' => 2, 'date' => '1998-02-15', 'role' => 'Pilote titulaire'],
                    ['prenom' => 'Frederik', 'nom' => 'Vesti', 'points_license' => 0, 'date' => '2002-07-19', 'role' => 'Pilote de réserve']
                ]
            ],
            [
                'nom' => 'Scuderia Ferrari',
                'moteur' => 'Ferrari',
                'pilotes' => [
                    ['prenom' => 'Charles', 'nom' => 'Leclerc', 'points_license' => 4, 'date' => '1997-10-16', 'role' => 'Pilote titulaire'],
                    ['prenom' => 'Carlos', 'nom' => 'Sainz Jr', 'points_license' => 3, 'date' => '1994-09-01', 'role' => 'Pilote titulaire'],
                    ['prenom' => 'Oliver', 'nom' => 'Bearman', 'points_license' => 0, 'date' => '2005-05-08', 'role' => 'Pilote de réserve']
                ]
            ],
            [
                'nom' => 'McLaren F1 Team',
                'moteur' => 'Mercedes',
                'pilotes' => [
                    ['prenom' => 'Lando', 'nom' => 'Norris', 'points_license' => 2, 'date' => '1999-11-13', 'role' => 'Pilote titulaire'],
                    ['prenom' => 'Oscar', 'nom' => 'Piastri', 'points_license' => 1, 'date' => '2001-04-06', 'role' => 'Pilote titulaire'],
                    ['prenom' => 'Pato', 'nom' => 'O\'Ward', 'points_license' => 0, 'date' => '1999-05-06', 'role' => 'Pilote de réserve']
                ]
            ]
        ];

        foreach ($ecuries as $ecurieData) {
            $ecurie = new Ecurie();
            $ecurie->setNom($ecurieData['nom']);
            $ecurie->setMoteur($ecurieData['moteur']);
            
            $manager->persist($ecurie);

            foreach ($ecurieData['pilotes'] as $piloteData) {
                $pilote = new Pilote();
                $pilote->setPrenom($piloteData['prenom']);
                $pilote->setNom($piloteData['nom']);
                $pilote->setPointsLicense($piloteData['points_license']);
                $pilote->setDate(new \DateTime($piloteData['date']));
                $pilote->setRole($piloteData['role']);
                $pilote->setEcurie($ecurie);
                
                $manager->persist($pilote);
            }
        }

        $manager->flush();
    }
}