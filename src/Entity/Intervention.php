<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $datePrevue = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateRealisation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaire = null;

    /**
     * @var Collection<int, devis>
     */
    #[ORM\OneToMany(targetEntity: Devis::class, mappedBy: 'intervention')]
    private Collection $devis;

    /**
     * @var Collection<int, terrain>
     */
    #[ORM\OneToMany(targetEntity: Terrain::class, mappedBy: 'intervention')]
    private Collection $terrain;

    #[ORM\ManyToOne(inversedBy: 'intervention')]
    private ?MaterielUtilise $materielUtilise = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    private ?EquipeIntervention $equipeIntevention = null;

    public function __construct()
    {
        $this->devis = new ArrayCollection();
        $this->terrain = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePrevue(): ?\DateTimeImmutable
    {
        return $this->datePrevue;
    }

    public function setDatePrevue(\DateTimeImmutable $datePrevue): static
    {
        $this->datePrevue = $datePrevue;

        return $this;
    }

    public function getDateRealisation(): ?\DateTimeImmutable
    {
        return $this->dateRealisation;
    }

    public function setDateRealisation(\DateTimeImmutable $dateRealisation): static
    {
        $this->dateRealisation = $dateRealisation;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * @return Collection<int, devis>
     */
    public function getDevis(): Collection
    {
        return $this->devis;
    }

    public function addDevi(Devis $devi): static
    {
        if (!$this->devis->contains($devi)) {
            $this->devis->add($devi);
            $devi->setIntervention($this);
        }

        return $this;
    }

    public function removeDevi(Devis $devi): static
    {
        if ($this->devis->removeElement($devi)) {
            // set the owning side to null (unless already changed)
            if ($devi->getIntervention() === $this) {
                $devi->setIntervention(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, terrain>
     */
    public function getTerrain(): Collection
    {
        return $this->terrain;
    }

    public function addTerrain(Terrain $terrain): static
    {
        if (!$this->terrain->contains($terrain)) {
            $this->terrain->add($terrain);
            $terrain->setIntervention($this);
        }

        return $this;
    }

    public function removeTerrain(Terrain $terrain): static
    {
        if ($this->terrain->removeElement($terrain)) {
            // set the owning side to null (unless already changed)
            if ($terrain->getIntervention() === $this) {
                $terrain->setIntervention(null);
            }
        }

        return $this;
    }

    public function getMaterielUtilise(): ?MaterielUtilise
    {
        return $this->materielUtilise;
    }

    public function setMaterielUtilise(?MaterielUtilise $materielUtilise): static
    {
        $this->materielUtilise = $materielUtilise;

        return $this;
    }

    public function getEquipeIntevention(): ?EquipeIntervention
    {
        return $this->equipeIntevention;
    }

    public function setEquipeIntevention(?EquipeIntervention $equipeIntevention): static
    {
        $this->equipeIntevention = $equipeIntevention;

        return $this;
    }
}
