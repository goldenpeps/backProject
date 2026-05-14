<?php

namespace App\Entity;

use App\Repository\MaterielRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaterielRepository::class)]
class Materiel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $disponible = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeMateriel $typeMateriel = null;

    #[ORM\ManyToOne(inversedBy: 'Materiel')]
    private ?MaterielUtilise $materielUtilise = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDisponible(): ?bool
    {
        return $this->disponible;
    }

    public function setDisponible(bool $disponible): static
    {
        $this->disponible = $disponible;

        return $this;
    }

    public function getTypeMateriel(): ?TypeMateriel
    {
        return $this->typeMateriel;
    }

    public function setTypeMateriel(?TypeMateriel $typeMateriel): static
    {
        $this->typeMateriel = $typeMateriel;

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
}
