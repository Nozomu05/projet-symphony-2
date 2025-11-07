<?php

namespace App\Entity;

use App\Repository\RegistreFractionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegistreFractionsRepository::class)]
class RegistreFractions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $penalite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $amende = null;

    #[ORM\Column(length: 50)]
    private ?string $nom_de_la_course = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne(inversedBy: 'registre_infractions')]
    private ?Ecurie $ecurie = null;

    #[ORM\ManyToOne(inversedBy: 'registre_infractions')]
    private ?Pilote $pilote = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPenalite(): ?int
    {
        return $this->penalite;
    }

    public function setPenalite(?int $penalite): static
    {
        $this->penalite = $penalite;

        return $this;
    }

    public function getAmende(): ?string
    {
        return $this->amende;
    }

    public function setAmende(?string $amende): static
    {
        $this->amende = $amende;

        return $this;
    }

    public function getNomDeLaCourse(): ?string
    {
        return $this->nom_de_la_course;
    }

    public function setNomDeLaCourse(string $nom_de_la_course): static
    {
        $this->nom_de_la_course = $nom_de_la_course;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getEcurie(): ?Ecurie
    {
        return $this->ecurie;
    }

    public function setEcurie(?Ecurie $ecurie): static
    {
        $this->ecurie = $ecurie;

        return $this;
    }

    public function getPilote(): ?Pilote
    {
        return $this->pilote;
    }

    public function setPilote(?Pilote $pilote): static
    {
        $this->pilote = $pilote;

        return $this;
    }
}
