<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GameRepository::class)
 */
class Game
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $partie_score_final = [];

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="games")
     * @ORM\JoinTable(name="game_user_1")
     * )
     */
    private $joueur1;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="games")
     * @ORM\JoinTable (name="game_user_2")
     */
    private $joueur2;

    /**
     * @ORM\OneToOne(targetEntity=PlayUser::class, mappedBy="Game", cascade={"persist", "remove"})
     */
    private $gameUser;

    public function __construct()
    {
        $this->joueur1 = new ArrayCollection();
        $this->joueur2 = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPartieScoreFinal(): ?array
    {
        return $this->partie_score_final;
    }

    public function setPartieScoreFinal(array $partie_score_final): self
    {
        $this->partie_score_final = $partie_score_final;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getJoueur1(): Collection
    {
        return $this->joueur1;
    }

    public function addJoueur1(User $joueur1): self
    {
        if (!$this->joueur1->contains($joueur1)) {
            $this->joueur1[] = $joueur1;
        }

        return $this;
    }

    public function removeJoueur1(User $joueur1): self
    {
        if ($this->joueur1->contains($joueur1)) {
            $this->joueur1->removeElement($joueur1);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getJoueur2(): Collection
    {
        return $this->joueur2;
    }

    public function addJoueur2(User $joueur2): self
    {
        if (!$this->joueur2->contains($joueur2)) {
            $this->joueur2[] = $joueur2;
        }

        return $this;
    }

    public function removeJoueur2(User $joueur2): self
    {
        if ($this->joueur2->contains($joueur2)) {
            $this->joueur2->removeElement($joueur2);
        }

        return $this;
    }

    public function getGameUser(): ?PlayUser
    {
        return $this->gameUser;
    }

    public function setGameUser(PlayUser $gameUser): self
    {
        $this->gameUser = $gameUser;

        // set the owning side of the relation if necessary
        if ($gameUser->getGame() !== $this) {
            $gameUser->setGame($this);
        }

        return $this;
    }
}
