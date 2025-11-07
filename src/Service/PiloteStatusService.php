<?php

namespace App\Service;

use App\Entity\Pilote;
use App\Entity\RegistreFractions;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: RegistreFractions::class)]
class PiloteStatusService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function postPersist(RegistreFractions $infraction, LifecycleEventArgs $event): void
    {
        $pilote = $infraction->getPilote();
        
        if (!$pilote) {
            return;
        }

        $penalite = $infraction->getPenalite();
        if ($penalite && $penalite > 0) {
            $nouveauxPoints = $pilote->getPointsLicense() - $penalite;
            $pilote->setPointsLicense(max(0, $nouveauxPoints));
        }

        $this->updatePiloteStatus($pilote);
        
        $em = $event->getObjectManager();
        $em->persist($pilote);
        $em->flush();
    }

    public function updatePiloteStatus(Pilote $pilote): void
    {
        if ($pilote->getPointsLicense() < 1) {
            $pilote->setStatus('suspendu');
        } elseif ($pilote->getStatus() === 'suspendu' && $pilote->getPointsLicense() >= 1) {
            $pilote->setStatus('actif');
        }
    }

}