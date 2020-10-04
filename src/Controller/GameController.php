<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\PlayUser;
use App\Entity\Token;
use App\Repository\CardRepository;
use App\Repository\GameRepository;
use App\Repository\PlayUserRepository;
use App\Repository\SpecialTokenRepository;
use App\Repository\TokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;


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
    public function index(
        PlayUserRepository $playUserRepository,
        UserInterface $user,
        GameRepository $gameRepository
    )
    {


        $partieEnCours = $gameRepository->findBy(
            ['statut' => 0],
            ['id' => 'DESC']
        );

        $partieEnCoursJoueur = $playUserRepository->findBy(
            ['Game' => $partieEnCours],

            ['id' => 'DESC']
        );

        return $this->render('game/index.html.twig', [
            'controller_name' => 'GameController',
            'joueurConnected' => $this->getUser(),
            'partieEnCours' => $partieEnCours
        ]);
    }

    /**
     * @Route("/select-adversaire", name="game_select_adversaire")
     */
    public function selectAdversaire(UserRepository $userRepository)
    {

        $users = $userRepository->findAll();

        return $this->render('game/selectAdversaire.html.twig', [
            'users' => $users,
            'joueurConnected' => $this->getUser()
        ]);
    }

    /**
     * @Route("/show-game/{game}", name="show_game")
     * @param CardRepository $cardRepository
     * @param Game $game
     * @param GameRepository $gameRepository
     * @param TokenRepository $tokenRepository
     * @param SpecialTokenRepository $specialTokenRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showGame(
        CardRepository $cardRepository,
        Game $game,
        GameRepository $gameRepository,
        TokenRepository $tokenRepository,
        SpecialTokenRepository $specialTokenRepository
    )
    {
        $cards = $cardRepository->findAll();

        $tableauCards = [];
        foreach ($cards as $card) {
            $tableauCards[$card->getId()] = $card;
        }
        $pioche = [];
        foreach ($cards as $piocheCard) {
            $pioche[$piocheCard->getId()] = $piocheCard;
        }

        $tableauTokens = $tokenRepository->findArrayById();
        $terrain = [];
        foreach ($cards as $terrainCard) {
            $terrain[$terrainCard->getId()] = $terrainCard;
        }
        $tableauSpecialToken = $specialTokenRepository->findArrayById();


        return $this->render('game/showGame.html.twig', [
            'game' => $game,
            'joueur1' => $game->getGameUser()[0],
            'joueur2' => $game->getGameUser()[1],
            'tableauCards' => $tableauCards,
            'joueurConnected' => $this->getUser(),
            'pioche' => $pioche,
            'terrain' => $terrain,
            'tableauTokens' => $tableauTokens,
            'tableauSpecialToken' => $tableauSpecialToken
        ]);
    }

    /**
     * @Route("/new-game", name="create_new_game")
     */
    public function newGame(
        EntityManagerInterface $entityManager,
        CardRepository $cardRepository,
        TokenRepository $tokenRepository,
        UserRepository $userRepository,
        SpecialTokenRepository $specialTokenRepository,
        Request $request)
    {
        $idAdversaire = $request->request->get('adversaire');
        $adversaire = $userRepository->find($idAdversaire);

        //Récupération des cartes
        $cards = $cardRepository->findAll();
        /*foreach ($cards as $card) {
            $tableauCards[] = $card->getId();
        }
        shuffle($tableauCards);*/


        //Récupéation des jetons
        $tokens = $tokenRepository->findBy([], array('value' => 'ASC'));

        //Récupération des jetons spéciaux
        $specialTokens = $specialTokenRepository->findAll();




        //Création des tableaux de données
        $tableauCards = [];
        $tableauTokens = [];
        $tableauSpecialTokens = [3=>[],4=>[],5=>[]];

        foreach (Token::RESSOURCE as $ressource) {
            $tableauTokens[$ressource] = [];
        }
        foreach ($tokens as $token) {
            $tableauTokens[$token->getRessource()][] = $token->getId();
        }
        foreach ($specialTokens as $token)
        {
            $tableauSpecialTokens[$token->getNbCards()][] = $token->getId();
        }

        //Mélange des tableau de jetons spéciaux
        shuffle($tableauSpecialTokens[3]);
        shuffle($tableauSpecialTokens[4]);
        shuffle($tableauSpecialTokens[5]);


        //Création du terrain
        $terrain = [];
        $max = 0;
        //Ajout de trois mammouth en début de partie sur le terrain
        foreach ($cards as $card) {
            if ($card->getCamel() === true && $max < 3) {
                $terrain[] = $card->getId();
                $max++;
            } else {
                $tableauCards[] = $card->getId();
            }
        }

        shuffle($tableauCards);

        //Création de la partie
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


        //Ajout d'une interface pour le joueur 2

        $playJoueur2 = new PlayUser();
        //Assignation de la partie à l'interface
        $playJoueur2->setGame($game);
        $playJoueur2->setUser($adversaire);
        //on distribue 5 cartes au joueur 2
        $mainJ2 = [];
        for ($i = 0; $i < 5; $i++) {
            $mainJ2[] = array_pop($tableauCards);

        }

        //Assignation des 5 cartes au joueur 2
        $playJoueur2->setDeck($mainJ2);

        $terrain[] = array_pop($tableauCards);
        $terrain[] = array_pop($tableauCards);

        $playJoueur1->setCamel(0);
        $playJoueur2->setCamel(0);
        $game->setTerrain($terrain);

        $game->setPioche($tableauCards);

        $game->setTokens($tableauTokens);
        $game->setSpecialTokens($tableauSpecialTokens);

        $game->setStatut(0);


        //Persist des données
        $entityManager->persist($game);
        $entityManager->persist($playJoueur1);
        $entityManager->persist($playJoueur2);


        $entityManager->flush();

        return $this->render('game/new.html.twig', [
            'joueurConnected' => $this->getUser()
        ]);
    }
}
