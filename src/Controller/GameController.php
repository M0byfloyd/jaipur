<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameUser;
use App\Repository\CardRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;



/**
 * Class GameController
 * @package App\Controller
 * @Route("/game")
 */

class GameController extends AbstractController
{
    /**
     * @Route("/", name="game")
     */
    public function index()
    {
        return $this->render('game/index.html.twig', [
            'controller_name' => 'GameController',
        ]);
    }
    /**
     * @Route("/select-adversaire", name="game_select_adversaire")
     */
    public function selectAdversaire(UserRepository $userRepository)
    {

        $users = $userRepository->findAll();

        return $this->render('game/selectAdversaire.html.twig', [
            'users' => $users
        ]);
    }
    /**
     * @Route("/show-game/{game}", name="show_game")
     */
    public function showGame(
        CardRepository $cardRepository,
        Game $game)
    {
        $cards = $cardRepository->findAll();
        $tableauCards = [];
        foreach ($cards as $card)
        {
            $tableauCards[$card->getId()] = $card;
        }

        return $this->render('game/showGame.html.twig', [
            'game' => $game,
            'joueur1' => $game->getPlayGames()[0],
            'joueur2' => $game->getPlayGames()[1],
            'tableauCards' => $tableauCards
        ]);
    }

    /**
     * @Route("/new-game", name="create_new_game")
     */
    public function newGame(
        EntityManagerInterface $entityManager,
        CardRepository $cardRepository,
        UserRepository $userRepository,
        Request $request)
    {
        $idAdversaire = $request->request->get('adversaire');
        $adversaire = $userRepository->find($idAdversaire);

        //récupération de toutes les cartes
        $cards = $cardRepository->findAll();
        $tableauCards = [];

        foreach ($cards as $card)
        {
            $tableauCards[] = $card->getId();
        }

        shuffle($tableauCards);

        $game = new Game();
        $gameJoueur1 = new GameUser();
        $gameJoueur1->setGame($game);
        $gameJoueur1->setUser($this->getUser());
        //on distribue 5 cartes au joueur 1
        $mainJ1 = [];
        for ($i = 0; $i < 5; $i++){
            $mainJ1[] = array_pop($tableauCards);
        }
        $gameJoueur1->setDeck($mainJ1);

        $gameJoueur2 = new GameUser();
        $gameJoueur2->setGame($game);
        $gameJoueur2->setUser($adversaire);
        //on distribue 5 cartes au joueur 2
        $mainJ2 = [];
        for ($i = 0; $i < 5; $i++){
            $mainJ2[] = array_pop($tableauCards);

        }
        $gameJoueur2->setDeck($mainJ2);

        $entityManager->persist($game);
        $entityManager->persist($gameJoueur1);
        $entityManager->persist($gameJoueur2);
        $entityManager->flush();

        return $this->render('game/new.html.twig', [
        ]);
    }
}
