<?php

namespace App\Entity;

use App\Repository\TypePrestationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypePrestationRepository::class)]
class TypePrestation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Description = null;

    #[ORM\Column]
    private ?float $prixUnitaire = null;

    /**
     * @var Collection<int, LigneDevis>
     */
    #[ORM\OneToMany(targetEntity: LigneDevis::class, mappedBy: 'typePrestation')]
    private Collection $ligneDevis;

    public function __construct()
    {
        $this->ligneDevis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getPrixUnitaire(): ?float
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(float $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;

        return $this;
    }

    /**
     * @return Collection<int, LigneDevis>
     */
    public function getLigneDevis(): Collection
    {
        return $this->ligneDevis;
    }

    public function addLigneDevis(LigneDevis $ligneDevis): static
    {
        if (!$this->ligneDevis->contains($ligneDevis)) {
            $this->ligneDevis->add($ligneDevis);
            $ligneDevis->setTypePrestation($this);
        }

        return $this;
    }

    public function removeLigneDevis(LigneDevis $ligneDevis): static
    {
        if ($this->ligneDevis->removeElement($ligneDevis)) {
            // set the owning side to null (unless already changed)
            if ($ligneDevis->getTypePrestation() === $this) {
                $ligneDevis->setTypePrestation(null);
            }
        }

        return $this;
    }
}
