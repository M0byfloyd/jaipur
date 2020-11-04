<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\PlayUser;
use App\Entity\Token;
use App\Form\GameType;
use App\Repository\CardRepository;
use App\Repository\GameRepository;
use App\Repository\PlayUserRepository;
use App\Repository\SpecialTokenRepository;
use App\Repository\TokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class GameController
 * @package App\Controller
 * @Route("/")
 */
class GameController extends AbstractController
{
    function sendData($check = "erreur", $message = "message non formaté")
    {
        $data = [
            'check' => $check,
            'message' => $message
        ];
        return $data;
    }

    function defJoueurActif($game)
    {

        $joueurs = $game->getGameUser();
        if ($game->getJoueurActif() == 0 && $joueurs[0]->getUser()->getId() == $this->getUser()->getId()) {
            $game->setJoueurActif(1);
        } elseif ($game->getJoueurActif() == 1 && $joueurs[1]->getUser()->getId() == $this->getUser()->getId()) {
            $game->setJoueurActif(0);
        }
    }

    function remiseTerrain($game)
    {

        $recupCarteTerrain = $game->getTerrain();
        foreach ($recupCarteTerrain as $item) {
            $renvoisCarteTerrain[] = $item;
        }
        if (count($renvoisCarteTerrain) < 5) {
            $pioche = $game->getPioche();
            for ($i = 0; $i <= 5 - count($renvoisCarteTerrain); $i++) {
                $renvoisCarteTerrain[] = array_pop($pioche);
            }
        } else {
            $pioche = $game->getPioche();
        }
        $game->setPioche($pioche);
        $game->setTerrain($renvoisCarteTerrain);
    }
    //CRUD

    /**
     * @Route("admin/game", name="game_index", methods={"GET"})
     */
    public function index(GameRepository $gameRepository): Response
    {


        return $this->render('game/index.html.twig', [
            'games' => $gameRepository->partieJoueur($this->getUser()->getId()),
            'joueurConnected' => $this->getUser()

        ]);
    }

    /**
     * @Route("admin/game/new", name="game_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($game);
            $entityManager->flush();

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/new.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
            'joueurConnected' => $this->getUser()

        ]);
    }

    /**
     * @Route("admin/game/{id}", name="game_show", methods={"GET"})
     */
    public function show(Game $game): Response
    {
        return $this->render('game/show.html.twig', [
            'game' => $game,
            'joueurConnected' => $this->getUser()

        ]);
    }

    /**
     * @Route("admin/game/{id}/edit", name="game_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Game $game): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
            'joueurConnected' => $this->getUser()

        ]);
    }

    /**
     * @Route("admin/game/{id}", name="game_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Game $game): Response
    {
        if ($this->isCsrfTokenValid('delete' . $game->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($game);
            $entityManager->flush();
        }

        return $this->redirectToRoute('game_index');
    }

    //GERATION

    /**
     * @Route("/game", name="game")
     */
    public function accueil(
        PlayUserRepository $playUserRepository,
        UserInterface $user,
        GameRepository $gameRepository
    )
    {
        $partieEnCours = $gameRepository->findBy(
            ['statut' => 0],
            ['id' => 'DESC']
        );
        return $this->render('game/accueil.html.twig', [
            'controller_name' => 'GameController',
            'joueurConnected' => $this->getUser(),
            'partieEnCours' => $partieEnCours
        ]);
    }

    /**
     * @Route("game/select-adversaire", name="game_select_adversaire")
     */
    public function selectAdversaire(UserRepository $userRepository)
    {

        //$users = $userRepository->findByRole('ROLE_JOUEUR');
        $users = $userRepository->selectJoueur($this->getUser()->getLogin());
        return $this->render('game/selectAdversaire.html.twig', [
            'users' => $users,
            'joueurConnected' => $this->getUser()
        ]);
    }

    /**
     * @Route("game/show-game/{game}", name="show_game")
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
     * @Route("game/new-game", name="create_new_game")
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

        $listeChammeaux = $cardRepository->findBy(
            ['ressources' => 'mammouth']
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

        dump($listeChammeaux);
        dump($mainJ1);
        // Pour chaque chamal dans chammeaux
        foreach ($listeChammeaux as $chammal) {
            dump('chamal id' . $chammal->getId());
            dump(array_search($chammal->getId(), $mainJ1));
            if (array_search($chammal->getId(), $mainJ1) != false) {
                dump('Il faut supprimer l\'index ' . array_search($chammal->getId(), $mainJ1));
                array_splice($mainJ1, array_search($chammal->getId(), $mainJ1), 1);
                $playJoueur1->setCamel($playJoueur1->getCamel() + 1);
            }
        }

        //Assignation des 5 cartes au joueur 1
        $playJoueur1->setDeck($mainJ1);

        dump($playJoueur1);

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

        foreach ($listeChammeaux as $chammal) {
            dump('chamal id' . $chammal->getId());
            dump(array_search($chammal->getId(), $mainJ2));
            if (array_search($chammal->getId(), $mainJ2) != false) {
                dump('Il faut supprimer l\'index ' . array_search($chammal->getId(), $mainJ2));
                array_splice($mainJ2, array_search($chammal->getId(), $mainJ2), 1);
                $playJoueur2->setCamel($playJoueur2->getCamel() + 1);
            }
        }
        //Assignation des 5 cartes au joueur 2

        $playJoueur2->setDeck($mainJ2);

        $playJoueur1->setPoints(0);
        $playJoueur1->setMancheWin(0);
        $playJoueur2->setPoints(0);
        $playJoueur2->setMancheWin(0);

        $terrain[] = array_pop($tableauCards);
        $terrain[] = array_pop($tableauCards);


        $game->setTerrain($terrain);

        $game->setPioche($tableauCards);

        $game->setTokens($tableauTokens);
        $game->setSpecialTokens($tableauSpecialTokens);

        $game->setStatut(0);
        $game->setJoueurActif(0);

        //Persist des données
        $entityManager->persist($game);
        $entityManager->persist($playJoueur1);
        $entityManager->persist($playJoueur2);


        $entityManager->flush();

        return $this->render('game/newGame.html.twig', [
            'joueurConnected' => $this->getUser(),
            'game_id' => $game->getId(),
        ]);
    }

    //PARTIE

    /**
     * @Route("game/quijoue/{game}", name="qui_joue")
     */
    public function quiJoue(Game $game)
    {
        $joueurs = $game->getGameUser();

        if ($game->getJoueurActif() == 0 && $joueurs[0]->getUser()->getId() == $this->getUser()->getId()) {
            return $this->json('jejoue');
        } elseif ($game->getJoueurActif() == 1 && $joueurs[1]->getUser()->getId() == $this->getUser()->getId()) {
            return $this->json('jejoue');
        } else {
            return $this->json('monadversairejoue');
        }

    }

    /**
     * @Route("game/refresh-plateau/{game}", name="refresh_plateau")
     */
    public function refreshPlateau(
        EntityManagerInterface $entityManager,
        CardRepository $cardRepository,
        TokenRepository $tokenRepository,
        SpecialTokenRepository $specialTokenRepository, Game $game)
    {
        $tableauCards = $cardRepository->findArrayById();
        $tableauTokens = $tokenRepository->findArrayById();
        $tableauSpecialToken = $specialTokenRepository->findArrayById();
        $this->remiseTerrain($game);

        function videOuNon($ressJeton)
        {
            $vide = 0;
            foreach ($ressJeton as $resource) {
                if (empty($resource)) {
                    $vide++;
                }
            }
            return $vide;
        }

        $tabJetons = $game->getTokens();
        if (videOuNon($tabJetons) >= 3) {
            $joueur1 = $game->getGameUser()[0];
            $joueur2 = $game->getGameUser()[1];

            if ($joueur1->getPoints() !== $joueur2->getPoints()) {
                if ($joueur1->getPoints() > $joueur2->getPoints()) {
                    $joueur1->setMancheWin($joueur1->getManche() + 1);
                    $gagnant = $joueur1->getUser()->getLogin();
                } else {
                    $joueur2->setMancheWin($joueur2->getMancheWin() + 1);
                    $gagnant = $joueur2->getUser()->getLogin();
                }
            } else {
                $finManche = 'égalité';
            }
            //Récupération des cartes
            $cards = $cardRepository->findAll();

            $listeChammeaux = $cardRepository->findBy(
                ['ressources' => 'mammouth']
            );


            //Récupéation des jetons
            $tokens = $tokenRepository->findBy([], array('value' => 'ASC'));

            //Récupération des jetons spéciaux
            $specialTokens = $specialTokenRepository->findAll();


            //Création des tableaux de données
            $tableauCards = [];
            $tableauTokens = [];
            $tableauSpecialTokens = [
                3 => [],
                4 => [],
                5 => []
            ];

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


            //Distribution de 5 cartes au joueur 1
            $joueur1->setCamel(0);
            $mainJ1 = [];
            for ($i = 0; $i < 5; $i++) {
                $mainJ1[] = array_pop($tableauCards);
            }

            //Vérification chammeaux


            // Pour chaque chamal dans
            foreach ($listeChammeaux as $chammal) {
                dump('chamal id' . $chammal->getId());
                dump(array_search($chammal->getId(), $mainJ1));
                if (array_search($chammal->getId(), $mainJ1) != false) {
                    dump('Il faut supprimer l\'index ' . array_search($chammal->getId(), $mainJ1));
                    array_splice($mainJ1, array_search($chammal->getId(), $mainJ1), 1);
                    $joueur1->setCamel($joueur1->getCamel() + 1);
                }
            }

            //Assignation des 5 cartes au joueur 1
            $joueur1->setDeck($mainJ1);

            //Ajout d'une interface pour le joueur 2

            $joueur2->setCamel(0);

            //on distribue 5 cartes au joueur 2
            $mainJ2 = [];
            for ($i = 0; $i < 5; $i++) {
                $mainJ2[] = array_pop($tableauCards);

            }

            //Vérification chammeaux

            foreach ($listeChammeaux as $chammal) {
                dump('chamal id' . $chammal->getId());
                dump(array_search($chammal->getId(), $mainJ2));
                if (array_search($chammal->getId(), $mainJ2) != false) {
                    dump('Il faut supprimer l\'index ' . array_search($chammal->getId(), $mainJ2));
                    array_splice($mainJ2, array_search($chammal->getId(), $mainJ2), 1);
                    $joueur2->setCamel($joueur2->getCamel() + 1);
                }
            }
            //Assignation des 5 cartes au joueur 2

            $joueur2->setDeck($mainJ2);

            $joueur1->setPoints(0);
            $joueur2->setPoints(0);

            $terrain[] = array_pop($tableauCards);
            $terrain[] = array_pop($tableauCards);


            $game->setTerrain($terrain);

            $game->setPioche($tableauCards);

            $game->setTokens($tableauTokens);
            $game->setSpecialTokens($tableauSpecialTokens);

            $game->setStatut($game->getStatut() + 1);
            $game->setJoueurActif(0);

            //Persist des données
            $entityManager->persist($game);
            $entityManager->persist($joueur1);
            $entityManager->persist($joueur2);


            $entityManager->flush();
            return $this->json($gagnant);


        }

        $entityManager->flush();

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
     * @Route("game/action/fin", name="fin_partie")
     */
    public function finPartie(Request $request)
    {
        $finResultat = $request->attributes->get('finResultat');

        return $this->render('game/fin.html.twig', [
            'resultatPartie' => $finResultat
        ]);
    }

    /**
     * @Route("game/action/prendre/{game}", name="prendre_action")
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
        $playJoueur = $playUserRepository->findPlayUser($game->getId(), $user->getId());
        $playJoueur = $playJoueur[0];
        $tabJoueurDeck = $playJoueur->getDeck();

        //Récuoération carte envoyée
        $arrayTerrainRecept = $request->request->get('arrayTerrain');
        foreach ($arrayTerrainRecept as $item) {
            $arrayTerrain[] = intval($item);
        }
        foreach ($tabJoueurDeck as $item) {
            $tabJoueurDeckId[] = $item;
        }
        //Liste des mammouths
        $listeMammouths = $cardRepository->findBy(
            ['camel' => 1]
        );


        //return $this->json($arrayTerrain);
        //Si le joueur a pris plus d'une carte
        if (count($arrayTerrain) > 1) {
            return $this->json($this->sendData("error", 'Vous ne pouvez prendre qu\'une seule carte'));
        } else {
            $tabTerrain = $game->getTerrain();
            foreach ($listeMammouths as $chammal) {
                $mammouthCheckId[] = $chammal->getId();

                $mammouthCheck[] = array_search($chammal->getId(), $tabTerrain);
                if (array_search($chammal->getId(), $tabTerrain) !== false) {
                    array_splice($tabTerrain, array_search($chammal->getId(), $tabTerrain), 1);
                    $playJoueur->setCamel($playJoueur->getCamel() + 1);
                    $took = true;
                    $results[] = array_search($chammal->getId(), $tabTerrain);

                } else {
                    $refuse[] = 'nope';
                }
            }
            $dataMammoth = [
                'mammoth check' => $mammouthCheck,
                'tabTerrain' => $tabTerrain
            ];
            //return $this->json($dataMammoth);
            if (count($arrayTerrain) > (7 - count($tabJoueurDeckId))) {
                return $this->json($this->sendData("error", 'Vous ne pouvez pas posséder plus de 7 cartes'));
            } else {
                $tableauCards = $cardRepository->findArrayById();
                $tableauTokens = $tokenRepository->findArrayById();
                $tableauSpecialToken = $specialTokenRepository->findArrayById();
                if (!isset($took)) {
                    $numIndex = 0;
                    for ($i = 0; $i < count($tabTerrain); $i++) {
                        $numId = array_search($tabTerrain[$i], $arrayTerrain);
                        $resultatsNumId[] = $numId;
                        if ($numId !== false) {
                            $tabJoueurDeck[] = $tabTerrain[$numIndex];
                            array_splice($tabTerrain, $numIndex, 1);
                        }
                        $numIndex++;
                    }

                    //return $this->json($data);
                    $inArrayTests = [
                        'true' => in_array(true, $mammouthCheck),
                        '0' => in_array(0, $mammouthCheck),
                        '1' => in_array(1, $mammouthCheck),
                        '2' => in_array(2, $mammouthCheck),
                        '3' => in_array(3, $mammouthCheck),
                        '4' => in_array(4, $mammouthCheck),
                        '5' => in_array(5, $mammouthCheck),
                    ];
                    $data = [
                        'arrayTest' => $inArrayTests,
                        'arrayTerrain' => $arrayTerrain,
                        'numId' => $resultatsNumId,
                        'tabJoueurDeckFinal' => $tabJoueurDeck,
                        'tabTerrainFinal' => $tabTerrain
                    ];
                }

                //return $this->json($data);
                //return $this->json($tabJoueurDeck);
                $playJoueur->setDeck($tabJoueurDeck);


                $game->setTerrain($tabTerrain);

                $this->defJoueurActif($game);

                $entityManager->persist($game);
                $entityManager->persist($playJoueur);

                //Remise en place de la pioche

                //return $this->json($renvoisCarteTerrain);
            }
        }


        $entityManager->flush();

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

    //                                  VENDRE

    /**
     * @Route("game/action/vendre/{game}", name="vendre_action")
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
        $playJoueur = $playUserRepository->findPlayUser($game->getId(), $user->getId());
        $playJoueur = $playJoueur[0];
        $tabJoueurDeck = $playJoueur->getDeck();


        //Récuoération carte envoyée
        $arrayJoueur = $request->request->get('arrayJoueur');
        //return $this->json($arrayJoueur);

        for ($i = 0; $i <= count($arrayJoueur) - 1; $i++) {
            $sendCard[] = $cardRepository->find($arrayJoueur[$i]);
        }

        for ($i = 0; $i <= count($sendCard) - 1; $i++) {
            $sendCardRessource[] = $sendCard[$i]->getRessources();
        }

        //return $this->json($sendCardId);
        //return $this->json($sendCard);

        if (count($arrayJoueur) < 2) {
            return $this->json($this->sendData("error", 'Il vous faut sélectionner deux carte au moins'));
        } else {
            /*if (count($arrayJoueur) > (9 - count($tabJoueurDeck))) {
                return $this->json('tropdecarte');
            }*/
            if (count(array_unique($sendCardRessource)) == count($sendCardRessource)) {
                return $this->json($this->sendData("error", 'Les cartes doivent être du même type'));
            } else {
                $tableauCards = $cardRepository->findArrayById();
                $tableauTokens = $tokenRepository->findArrayById();
                $tableauSpecialToken = $specialTokenRepository->findArrayById();
                $tabJoueurDeck = $playJoueur->getDeck();
                //return $this->json($sendCardRessource);

                foreach ($sendCard as $item) {
                    $sendCardId[] = intval($item->getId());
                }
                foreach ($tabJoueurDeck as $item) {
                    $tabJoueurDeckId[] = intval($item);
                }
                //return $this->json($tabJoueurDeckId);
                $numIndex = 0;

                for ($i = 0; $i <= count($tabJoueurDeckId); $i++) {
                    $isTrue = array_search($tabJoueurDeckId[$i], $sendCardId);
                    $isTrueCollec[] = $isTrue;
                    if ($isTrue !== false) {
                        $trueCollec[] = $isTrue;
                        $defausse[] = $tabJoueurDeckId[$i];
                        unset($tabJoueurDeckId[$i]);
                    }
                }
                array_values($tabJoueurDeckId);

                foreach ($tabJoueurDeckId as $item) {
                    $tabJoueurDeckFinal[] = $item;
                }
                //return $this->json($tabJoueurDeckId);
                //JETOOOOOONNN

                $pionsRessource = $gameRepository->findTokens($game->getId(), $sendCardRessource[0]);

                $dataTest = [
                    'trueCollec' => $trueCollec,
                    'isTrueCollec' => $isTrueCollec,
                    'deck de base' => $playJoueur->getDeck(),
                    'sendCard' => $sendCardId,
                    'deck' => $tabJoueurDeckId,
                ];

                for ($iterationCount = 0; $iterationCount < count($arrayJoueur); $iterationCount++) {
                    $der = count($pionsRessource) - 1;
                    $pionContext = $tokenRepository->find($pionsRessource[$der]);
                    $playJoueur->setPoints($playJoueur->getPoints() + $pionContext->getValue());
                    unset($pionsRessource[$der]);
                }
                //return $this->json(sendData('error',$pionsRessource));
                //return $this->json(sendData($gameRepository->arrayToken($game->getId(),$sendCardRessource[0],$pionsRessource)));

                $game->setTokens($gameRepository->arrayToken($game->getId(), $sendCardRessource[0], $pionsRessource));
                $playJoueur->setDeck($tabJoueurDeckFinal);

                $game->setDefausse($defausse);

                $this->defJoueurActif($game);
                $entityManager->persist($game);
                $entityManager->persist($playJoueur);
            }
        }
        $entityManager->flush();

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

    // ECHANGER

    /**
     * @Route("game/action/echange/{game}", name="echange_action")
     */
    public function echangeAction(
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
        $tableauCards = $cardRepository->findArrayById();
        $tableauTokens = $tokenRepository->findArrayById();
        $tableauSpecialToken = $specialTokenRepository->findArrayById();

        //Récupération du deck du joueur
        $playJoueur = $playUserRepository->findPlayUser($game->getId(), $user->getId());
        $playJoueur = $playJoueur[0];
        $tabJoueurDeck = $playJoueur->getDeck();

        //Recup terrain
        $tabTerrain = $game->getTerrain();

        //Récuoération carte envoyée
        $arrayJoueur = $request->request->get('arrayJoueur');
        $arrayTerrain = $request->request->get('arrayTerrain');

        $terrainDeb = $tabTerrain;
        $joueurDeb = $tabJoueurDeck;
        //return $this->json($arrayJoueur);
        $cacheTabJoueurDeck = $tabJoueurDeck;
        $cacheTabTerrain = $tabTerrain;

        $tabJoueurDeckCache = $tabJoueurDeck;
        $tabTerrainCache = $tabTerrain;

        foreach ($arrayJoueur as $carteId) {
            $indexCartePourTerrain[] = array_search($carteId, $tabJoueurDeck);
        }
        foreach ($arrayJoueur as $carteId) {
            array_splice($tabJoueurDeck, array_search($carteId, $tabJoueurDeck), 1);
        }
        foreach ($indexCartePourTerrain as $indexCarte) {
            $carteIdPourTerrain[] = $tabJoueurDeckCache[$indexCarte];
            $offsetError[] = $indexCarte;
        }

        foreach ($arrayTerrain as $carteId) {
            $indexCartePourJoueur[] = array_search($carteId, $tabTerrain);
        }
        foreach ($arrayTerrain as $carteId) {
            array_splice($tabTerrain, array_search($carteId, $tabTerrain), 1);
        }
        foreach ($indexCartePourJoueur as $indexCarte) {
            $carteIdPourJoueur[] = $tabTerrainCache[$indexCarte];
            $offsetError[] = $indexCarte;
        }

        foreach ($carteIdPourJoueur as $carte) {
            $tabJoueurDeck[] = $carte;
        }

        foreach ($carteIdPourTerrain as $carte) {
            $tabTerrain[] = $carte;
        }

        $this->defJoueurActif($game);
        $game->setTerrain($tabTerrain);
        $playJoueur->setDeck($tabJoueurDeck);

        $entityManager->persist($game);
        $entityManager->persist($playJoueur);


        $entityManager->flush();

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

}
