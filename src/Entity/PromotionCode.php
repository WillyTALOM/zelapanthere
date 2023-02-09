<?php

namespace App\Entity;

use App\Repository\PromotionCodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromotionCodeRepository::class)]
class PromotionCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $Code_name = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeName(): ?string
    {
        return $this->Code_name;
    }

    public function setCodeName(?string $Code_name): self
    {
        $this->Code_name = $Code_name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }
}
