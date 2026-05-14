<?php

namespace App\Entity;

use App\Repository\LigneDevisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneDevisRepository::class)]
class LigneDevis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $Quantite = null;

    #[ORM\Column]
    private ?float $PrixLigne = null;

    #[ORM\ManyToOne(inversedBy: 'ligneDevis')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devis $devis = null;

    #[ORM\ManyToOne(inversedBy: 'ligneDevis')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypePrestation $typePrestation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->Quantite;
    }

    public function setQuantite(int $Quantite): static
    {
        $this->Quantite = $Quantite;

        return $this;
    }

    public function getPrixLigne(): ?float
    {
        return $this->PrixLigne;
    }

    public function setPrixLigne(float $PrixLigne): static
    {
        $this->PrixLigne = $PrixLigne;

        return $this;
    }

    public function getDevis(): ?Devis
    {
        return $this->devis;
    }

    public function setDevis(?Devis $devis): static
    {
        $this->devis = $devis;

        return $this;
    }

    public function getTypePrestation(): ?TypePrestation
    {
        return $this->typePrestation;
    }

    public function setTypePrestation(?TypePrestation $typePrestation): static
    {
        $this->typePrestation = $typePrestation;

        return $this;
    }
}
