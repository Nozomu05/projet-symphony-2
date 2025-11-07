<?php

namespace App\Entity;

use App\Repository\PiloteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PiloteRepository::class)]
class Pilote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $points_license = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(length: 25)]
    private ?string $role = null;

    #[ORM\ManyToOne(inversedBy: 'pilote')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ecurie $ecurie = null;

    /**
     * @var Collection<int, RegistreFractions>
     */
    #[ORM\OneToMany(targetEntity: RegistreFractions::class, mappedBy: 'pilote')]
    private Collection $registre_infractions;

    #[ORM\Column(length: 50, options: ['default' => 'actif'])]
    private ?string $status = 'actif';

    public function __construct()
    {
        $this->registre_infractions = new ArrayCollection();
        $this->status = 'actif';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
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

    public function getPointsLicense(): ?int
    {
        return $this->points_license;
    }

    public function setPointsLicense(int $points_license): static
    {
        $this->points_license = $points_license;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

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
            $registreInfraction->setPilote($this);
        }

        return $this;
    }

    public function removeRegistreInfraction(RegistreFractions $registreInfraction): static
    {
        if ($this->registre_infractions->removeElement($registreInfraction)) {
            // set the owning side to null (unless already changed)
            if ($registreInfraction->getPilote() === $this) {
                $registreInfraction->setPilote(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
