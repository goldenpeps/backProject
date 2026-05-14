<?php

namespace App\Entity;

use App\Entity\HistoriqueTerrain;
use App\Repository\TerrainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TerrainRepository::class)]
class Terrain
{
    public function __construct()
    {
        $this->historiqueTerrain = new ArrayCollection();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\Column]
    private ?float $superficie = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeTerrain $typeTerrain = null;

    #[ORM\OneToMany(mappedBy: 'terrain', targetEntity: HistoriqueTerrain::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $historiqueTerrain;

    #[ORM\ManyToOne(inversedBy: 'terrain')]
    private ?Intervention $intervention = null;

    /**
     * Adresse structurée du terrain
     * Structure: {
     *   "nom": "Nom du terrain",
     *   "rue": "Rue et numéro",
     *   "codePostal": "Code postal",
     *   "ville": "Ville"
     * }
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $adresse = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $coordonneesGps = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getSuperficie(): ?float
    {
        return $this->superficie;
    }

    public function setSuperficie(float $superficie): static
    {
        $this->superficie = $superficie;

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

    public function getTypeTerrain(): ?TypeTerrain
    {
        return $this->typeTerrain;
    }

    public function setTypeTerrain(?TypeTerrain $typeTerrain): static
    {
        $this->typeTerrain = $typeTerrain;

        return $this;
    }

    /**
     * @return Collection<int, HistoriqueTerrain>
     */
    public function getHistoriqueTerrain(): Collection
    {
        return $this->historiqueTerrain;
    }

    public function addHistoriqueTerrain(HistoriqueTerrain $historiqueTerrain): static
    {
        if (!$this->historiqueTerrain->contains($historiqueTerrain)) {
            $this->historiqueTerrain->add($historiqueTerrain);
            $historiqueTerrain->setTerrain($this);
        }

        return $this;
    }

    public function removeHistoriqueTerrain(HistoriqueTerrain $historiqueTerrain): static
    {
        if ($this->historiqueTerrain->removeElement($historiqueTerrain)) {
            if ($historiqueTerrain->getTerrain() === $this) {
                $historiqueTerrain->setTerrain(null);
            }
        }

        return $this;
    }

    public function getIntervention(): ?Intervention
    {
        return $this->intervention;
    }

    public function setIntervention(?Intervention $intervention): static
    {
        $this->intervention = $intervention;

        return $this;
    }

    public function getAdresse(): ?array
    {
        return $this->adresse;
    }

    public function setAdresse(?array $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCoordonneesGps(): ?array
    {
        return $this->coordonneesGps;
    }

    public function setCoordonneesGps(?array $coordonneesGps): static
    {
        $this->coordonneesGps = $coordonneesGps;

        return $this;
    }
}
