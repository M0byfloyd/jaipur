<?php

namespace App\Entity;

use App\Repository\PlayUserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlayUserRepository::class)
 *  @ORM\Table(name="play_user")
 */
class PlayUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="gameUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Game::class, inversedBy="gameUser", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $Game;

    /**
     * @ORM\Column(type="json")
     */
    private $deck = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $camel;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->Game;
    }

    public function setGame(Game $Game): self
    {
        $this->Game = $Game;

        return $this;
    }

    public function getDeck(): ?array
    {
        return $this->deck;
    }

    public function setDeck(array $deck): self
    {
        $this->deck = $deck;

        return $this;
    }

    public function getCamel(): ?int
    {
        return $this->camel;
    }

    public function setCamel(int $camel): self
    {
        $this->camel = $camel;

        return $this;
    }
}
