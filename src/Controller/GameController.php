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

        $users = $userRepository->findByRole('ROLE_JOUEUR');

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

        //Récupération liste chammeau

        $listeChammeaux= $cardRepository->findBy(
            ['ressources'=>'mammouth']
        );


        //Récupéation des jetons
        $tokens = $tokenRepository->findBy([], array('value' => 'ASC'));

        //Récupération des jetons spéciaux
        $specialTokens = $specialTokenRepository->findAll();


        //Création des tableaux de données
        $tableauCards = [];
        $tableauTokens = [];
        $tableauSpecialTokens = [3 => [], 4 => [], 5 => []];

        foreach (Token::RESSOURCE as $ressource) {
            $tableauTokens[$ressource] = [];
        }
        foreach ($tokens as $token) {
            $tableauTokens[$token->getRessource()][] = $token->getId();
        }
        foreach ($specialTokens as $token) {
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
        $playJoueur1->setCamel(0);
        //Assignation de la partie à l'interface
        $playJoueur1->setGame($game);
        $playJoueur1->setUser($this->getUser());
        //Distribution de 5 cartes au joueur 1
        $mainJ1 = [];
        for ($i = 0; $i < 5; $i++) {
            $mainJ1[] = array_pop($tableauCards);
        }

        //Vérification chammeaux

        foreach ($listeChammeaux AS $item) {
            $numId =array_search($item->getId(),$mainJ1);
            if (!empty($numId)) {
                unset($mainJ1[$numId]);
                $playJoueur1->setCamel($playJoueur1->getCamel()+1);
            }

        }

        //Assignation des 5 cartes au joueur 1
        $playJoueur1->setDeck($mainJ1);


        //Ajout d'une interface pour le joueur 2

        $playJoueur2 = new PlayUser();
        $playJoueur2->setCamel(0);
        //Assignation de la partie à l'interface
        $playJoueur2->setGame($game);
        $playJoueur2->setUser($adversaire);
        //on distribue 5 cartes au joueur 2
        $mainJ2 = [];
        for ($i = 0; $i < 5; $i++) {
            $mainJ2[] = array_pop($tableauCards);

        }

        //Vérification chammeaux

        foreach ($listeChammeaux AS $item) {
            $numId =array_search($item->getId(),$mainJ2);
            if (!empty($numId)) {
                unset($mainJ2[$numId]);
                $playJoueur2->setCamel($playJoueur2->getCamel()+1);
            }

        }

        //Assignation des 5 cartes au joueur 2

        $playJoueur2->setDeck($mainJ2);

        $terrain[] = array_pop($tableauCards);
        $terrain[] = array_pop($tableauCards);






        $game->setTerrain($terrain);

        $game->setPioche($tableauCards);

        $game->setTokens($tableauTokens);
        $game->setSpecialTokens($tableauSpecialTokens);

        $game->setStatut(0);
        $game->setJoueurActif(1);

        //Persist des données
        $entityManager->persist($game);
        $entityManager->persist($playJoueur1);
        $entityManager->persist($playJoueur2);


        $entityManager->flush();

        return $this->render('game/new.html.twig', [
            'joueurConnected' => $this->getUser(),
            'game_id'=>$game->getId(),
        ]);
    }

    //PARTIE

    /**
     * @Route("/quijoue/{game}", name="qui_joue")
     */
    public function quiJoue(Game $game)
    {
        $joueurs = $game->getGameUser();

        if ($game->getJoueurActif() == 0 && $joueurs[0]->getUser()->getId() == $this->getUser()->getId()) {
            return $this->json('jejoue');
        } else {
            return $this->json('monadversairejoue');
        }

    }

    /**
     * @Route("/refresh-plateau/{game}", name="refresh_plateau")
     */
    public function refreshPlateau(
        CardRepository $cardRepository,
        TokenRepository $tokenRepository,
        SpecialTokenRepository $specialTokenRepository, Game $game)
    {
        $tableauCards = $cardRepository->findArrayById();
        $tableauTokens = $tokenRepository->findArrayById();
        $tableauSpecialToken = $specialTokenRepository->findArrayById();

        return $this->render('game/plateau.html.twig', [
            'game' => $game,
            'joueur1' => $game->getGameUser()[0],
            'joueur2' => $game->getGameUser()[1],
            'joueurConnected' => $this->getUser(),
            'tableauCards' => $tableauCards,
            'tableauTokens' => $tableauTokens,
            'tableauSpecialToken' => $tableauSpecialToken
        ]);

    }

    /**
     * @Route("/action/prendre/{game}", name="prendre_action")
     */
    public function prendreAction(
        CardRepository $cardRepository,
        PlayUserRepository $playUserRepository,
        TokenRepository $tokenRepository,
        SpecialTokenRepository $specialTokenRepository,
        GameRepository $gameRepository,
        UserInterface $user,
        Game $game,
        Request $request,
        EntityManagerInterface $entityManager

    )
    {
        //Récupération du deck du joueur
        $playJoueur= $playUserRepository->findPlayUser($game->getId(),$user->getId());
        $playJoueur = $playJoueur[0];
        $tabJoueurDeck = $playJoueur->getDeck();

        //Récuoération carte envoyée
        $arrayTerrain = $request->request->get('arrayTerrain');
        //return $this->json($arrayTerrain);
        if (count($arrayTerrain) > 1) {
            return $this->json('unecarteseulement');
        }
        else {
            if ( count($arrayTerrain) > (7-count($tabJoueurDeck)) ) {
                return $this->json('tropdecarte');
            }
            else {
                $tableauCards = $cardRepository->findArrayById();
                $tableauTokens = $tokenRepository->findArrayById();
                $tableauSpecialToken = $specialTokenRepository->findArrayById();


                $tabTerrain = $gameRepository->getTerrain($game);


                for ($i = 0; $i < count($arrayTerrain); $i++) {

                    unset($tabTerrain[array_search($arrayTerrain[$i], $tabTerrain)]);
                    $tabJoueurDeck[] = $arrayTerrain[$i];
                }
                $playJoueur->setDeck($tabJoueurDeck);



                $game->setTerrain($tabTerrain);
                $game->setJoueurActif(0);
                $entityManager->persist($game);
                $entityManager->persist($playJoueur);

                //Remise en place de la pioche
                $recupCarteTerrain= $game->getTerrain();
                foreach ($recupCarteTerrain as $item) {
                    $renvoisCarteTerrain[] = $item;
                }

                //return $this->json($renvoisCarteTerrain);

                if (count($renvoisCarteTerrain) < 5) {
                    $pioche = $game->getPioche();
                    for ($i =0; $i <= 5-count($renvoisCarteTerrain); $i++) {
                        $renvoisCarteTerrain[]= array_pop($pioche);
                    }

                }

        }
            $game->setTerrain($renvoisCarteTerrain);
            $game->setPioche($pioche);


            $entityManager->flush();

            return $this->render('game/plateau.html.twig', [
                'game' => $game,
                'joueur1' => $game->getGameUser()[0],
                'joueur2' => $game->getGameUser()[1],
                'joueurConnected'=>$this->getUser(),
                'tableauCards' => $tableauCards,
                'tableauTokens' => $tableauTokens,
                'tableauSpecialToken' => $tableauSpecialToken
            ]);
        }



    }
    /**
     * @Route("/action/vendre/{game}", name="vendre_action")
     */
    public function vendreAction(
        CardRepository $cardRepository,
        PlayUserRepository $playUserRepository,
        TokenRepository $tokenRepository,
        SpecialTokenRepository $specialTokenRepository,
        GameRepository $gameRepository,
        UserInterface $user,
        Game $game,
        Request $request,
        EntityManagerInterface $entityManager

    )
    {
        //Récupération du deck du joueur
        $playJoueur= $playUserRepository->findPlayUser($game->getId(),$user->getId());
        $playJoueur = $playJoueur[0];
        $tabJoueurDeck = $playJoueur->getDeck();

        //Récuoération carte envoyée
        $arrayJoueur = $request->request->get('arrayJoueur');
        //return $this->json($arrayJoueur);

        for ($i =0; $i <= count($arrayJoueur)-1; $i++) {

            $sendCard[] =$cardRepository->find($arrayJoueur[$i]);
        }

        //return $this->json($sendCardId);
        //return $this->json($sendCard);
        if (count($arrayJoueur) < 2) {
            return $this->json('pasAssezCartesVendre');
        }
        else {
            if ( count($arrayJoueur) > (9-count($tabJoueurDeck)) ) {
                return $this->json('tropdecarte');
            }
            else {
                for ($i =0; $i <= count($sendCard)-1; $i++) {

                    $sendCardRessource[] = $sendCard[$i]->getRessources();
                }
                if (count(array_unique($sendCardRessource)) == count($sendCardRessource)) {
                    return $this->json('memeType');
                }
                else {
                    $tableauCards = $cardRepository->findArrayById();
                    $tableauTokens = $tokenRepository->findArrayById();
                    $tableauSpecialToken = $specialTokenRepository->findArrayById();
                    $tabJoueurDeck = $playJoueur->getDeck();
                    //return $this->json($sendCardRessource);

                    foreach ($sendCard as $item) {
                        $sendCardId[] = $item->getId();
                    }

                    foreach ($sendCard as $item) {
                        unset($tabJoueurDeck[array_search($item, $sendCard)]);
                        $sendCard[] = $item;
                    }

                    //return $this->json($sendCardId);
                    $playJoueur->setDeck($tabJoueurDeck);



                    $game->setJoueurActif(0);
                    $entityManager->persist($game);
                    $entityManager->persist($playJoueur);
                }


            }


            $entityManager->flush();

            return $this->render('game/plateau.html.twig', [
                'game' => $game,
                'joueur1' => $game->getGameUser()[0],
                'joueur2' => $game->getGameUser()[1],
                'joueurConnected'=>$this->getUser(),
                'tableauCards' => $tableauCards,
                'tableauTokens' => $tableauTokens,
                'tableauSpecialToken' => $tableauSpecialToken
            ]);
        }



    }
}
