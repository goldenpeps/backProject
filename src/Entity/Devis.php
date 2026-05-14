<?php

namespace App\Entity;

use App\Repository\DevisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DevisRepository::class)]
class Devis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $DateDevis = null;

    #[ORM\Column]
    private ?float $MontantTotal = null;

    #[ORM\Column(length: 255)]
    private ?string $Status = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    /**
     * @var Collection<int, LigneDevis>
     */
    #[ORM\OneToMany(targetEntity: LigneDevis::class, mappedBy: 'devis', orphanRemoval: true)]
    private Collection $ligneDevis;

    #[ORM\ManyToOne(inversedBy: 'devis')]
    private ?Intervention $intervention = null;

    public function __construct()
    {
        $this->ligneDevis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDevis(): ?\DateTimeImmutable
    {
        return $this->DateDevis;
    }

    public function setDateDevis(\DateTimeImmutable $DateDevis): static
    {
        $this->DateDevis = $DateDevis;

        return $this;
    }

    public function getMontantTotal(): ?float
    {
        return $this->MontantTotal;
    }

    public function setMontantTotal(float $MontantTotal): static
    {
        $this->MontantTotal = $MontantTotal;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->Status;
    }

    public function setStatus(string $Status): static
    {
        $this->Status = $Status;

        return $this;
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
            $ligneDevis->setDevis($this);
        }

        return $this;
    }

    public function removeLigneDevis(LigneDevis $ligneDevis): static
    {
        if ($this->ligneDevis->removeElement($ligneDevis)) {
            // set the owning side to null (unless already changed)
            if ($ligneDevis->getDevis() === $this) {
                $ligneDevis->setDevis(null);
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
}
