<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CardRepository::class)
 */
class Card
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="boolean")
     */
    private $camel;

    /**
     * @return mixed
     */
    public function getCamel()
    {
        return $this->camel;
    }

    /**
     * @param mixed $camel
     */
    public function setCamel($camel): void
    {
        $this->camel = $camel;
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ressources;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRessources(): ?string
    {
        return $this->ressources;
    }

    public function setRessources(?string $ressources): self
    {
        $this->ressources = $ressources;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
