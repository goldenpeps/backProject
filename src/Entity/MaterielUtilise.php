<?php

namespace App\Entity;

use App\Repository\MaterielUtiliseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaterielUtiliseRepository::class)]
class MaterielUtilise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $Durrer = null;

    /**
     * @var Collection<int, Materiel>
     */
    #[ORM\OneToMany(targetEntity: Materiel::class, mappedBy: 'materielUtilise')]
    private Collection $Materiel;

    /**
     * @var Collection<int, intervention>
     */
    #[ORM\OneToMany(targetEntity: intervention::class, mappedBy: 'materielUtilise')]
    private Collection $intervention;

    public function __construct()
    {
        $this->Materiel = new ArrayCollection();
        $this->intervention = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDurrer(): ?\DateTimeImmutable
    {
        return $this->Durrer;
    }

    public function setDurrer(\DateTimeImmutable $Durrer): static
    {
        $this->Durrer = $Durrer;

        return $this;
    }

    /**
     * @return Collection<int, Materiel>
     */
    public function getMateriel(): Collection
    {
        return $this->Materiel;
    }

    public function addMateriel(Materiel $materiel): static
    {
        if (!$this->Materiel->contains($materiel)) {
            $this->Materiel->add($materiel);
            $materiel->setMaterielUtilise($this);
        }

        return $this;
    }

    public function removeMateriel(Materiel $materiel): static
    {
        if ($this->Materiel->removeElement($materiel)) {
            // set the owning side to null (unless already changed)
            if ($materiel->getMaterielUtilise() === $this) {
                $materiel->setMaterielUtilise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, intervention>
     */
    public function getIntervention(): Collection
    {
        return $this->intervention;
    }

    public function addIntervention(intervention $intervention): static
    {
        if (!$this->intervention->contains($intervention)) {
            $this->intervention->add($intervention);
            $intervention->setMaterielUtilise($this);
        }

        return $this;
    }

    public function removeIntervention(intervention $intervention): static
    {
        if ($this->intervention->removeElement($intervention)) {
            // set the owning side to null (unless already changed)
            if ($intervention->getMaterielUtilise() === $this) {
                $intervention->setMaterielUtilise(null);
            }
        }

        return $this;
    }
}
