<?php

namespace App\Entity;

use App\Repository\EcurieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EcurieRepository::class)]
class Ecurie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $moteur = null;

    /**
     * @var Collection<int, Pilote>
     */
    #[ORM\OneToMany(targetEntity: Pilote::class, mappedBy: 'ecurie')]
    private Collection $pilote;

    /**
     * @var Collection<int, RegistreFractions>
     */
    #[ORM\OneToMany(targetEntity: RegistreFractions::class, mappedBy: 'ecurie')]
    private Collection $registre_infractions;

    public function __construct()
    {
        $this->pilote = new ArrayCollection();
        $this->registre_infractions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getMoteur(): ?string
    {
        return $this->moteur;
    }

    public function setMoteur(string $moteur): static
    {
        $this->moteur = $moteur;

        return $this;
    }

    /**
     * @return Collection<int, Pilote>
     */
    public function getPilote(): Collection
    {
        return $this->pilote;
    }

    public function addPilote(Pilote $pilote): static
    {
        if (!$this->pilote->contains($pilote)) {
            $this->pilote->add($pilote);
            $pilote->setEcurie($this);
        }

        return $this;
    }

    public function removePilote(Pilote $pilote): static
    {
        if ($this->pilote->removeElement($pilote)) {
            // set the owning side to null (unless already changed)
            if ($pilote->getEcurie() === $this) {
                $pilote->setEcurie(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RegistreFractions>
     */
    public function getRegistreInfractions(): Collection
    {
        return $this->registre_infractions;
    }

    public function addRegistreInfraction(RegistreFractions $registreInfraction): static
    {
        if (!$this->registre_infractions->contains($registreInfraction)) {
            $this->registre_infractions->add($registreInfraction);
            $registreInfraction->setEcurie($this);
        }

        return $this;
    }

    public function removeRegistreInfraction(RegistreFractions $registreInfraction): static
    {
        if ($this->registre_infractions->removeElement($registreInfraction)) {
            // set the owning side to null (unless already changed)
            if ($registreInfraction->getEcurie() === $this) {
                $registreInfraction->setEcurie(null);
            }
        }

        return $this;
    }
}
