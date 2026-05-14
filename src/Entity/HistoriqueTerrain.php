<?php

namespace App\Entity;

use App\Repository\HistoriqueTerrainRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueTerrainRepository::class)]
class HistoriqueTerrain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $ramassage = null;

    #[ORM\Column]
    private ?bool $tonte = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateRamassage = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateTonte = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueTerrain')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Terrain $terrain = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isRamassage(): ?bool
    {
        return $this->ramassage;
    }

    public function setRamassage(bool $ramassage): static
    {
        $this->ramassage = $ramassage;

        return $this;
    }

    public function isTonte(): ?bool
    {
        return $this->tonte;
    }

    public function setTonte(bool $tonte): static
    {
        $this->tonte = $tonte;

        return $this;
    }

    public function getDateRamassage(): ?\DateTimeImmutable
    {
        return $this->dateRamassage;
    }

    public function setDateRamassage(?\DateTimeImmutable $dateRamassage): static
    {
        $this->dateRamassage = $dateRamassage;

        return $this;
    }

    public function getDateTonte(): ?\DateTimeImmutable
    {
        return $this->dateTonte;
    }

    public function setDateTonte(?\DateTimeImmutable $dateTonte): static
    {
        $this->dateTonte = $dateTonte;

        return $this;
    }

    public function getTerrain(): ?Terrain
    {
        return $this->terrain;
    }

    public function setTerrain(?Terrain $terrain): static
    {
        $this->terrain = $terrain;

        return $this;
    }
}
