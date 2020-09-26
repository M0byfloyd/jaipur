<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Game;
use App\Entity\PlayUser;
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
        foreach ($cards as $card) {
            $tableauCards[$card->getId()] = $card;
        }

        return $this->render('game/showGame.html.twig', [
            'game' => $game,
            'joueur1' => $game->getPlayUser()[0],
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

        foreach ($cards as $card) {
            $tableauCards[] = $card->getId();
        }

        shuffle($tableauCards);


        //Création d'une partie
        $game = new Game();

        //Ajout d'une interface pour le joueur 1
        $playJoueur1 = new PlayUser();
        //Assignation de la partie à l'interface
        $playJoueur1->setGame($game);
        $playJoueur1->setUser($this->getUser());
        //Distribution de 5 cartes au joueur 1
        $mainJ1 = [];
        for ($i = 0; $i < 5; $i++) {
            $mainJ1[] = array_pop($tableauCards);
        }
        //Assignation des 5 cartes au joueur 1
        $playJoueur1->setDeck($mainJ1);

        $playJoueur2 = new PlayUser();
        $playJoueur2->setGame($game);
        $playJoueur2->setUser($adversaire);
        //on distribue 5 cartes au joueur 2
        $mainJ2 = [];
        for ($i = 0; $i < 5; $i++) {
            $mainJ2[] = array_pop($tableauCards);

        }
        $playJoueur2->setDeck($mainJ2);

        $entityManager->persist($game);
        $entityManager->persist($playJoueur1);
        $entityManager->persist($playJoueur2);

        dump($game);

        dump($this->getUser());
        dump($adversaire);

        dump($playJoueur1);
        dump($playJoueur2);


        dd($entityManager);

        $entityManager->flush();

        return $this->render('game/new.html.twig', [
        ]);
    }
}
