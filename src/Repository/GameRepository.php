<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    // /**
    //  * @return Game[] Returns an array of Game objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Game
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function partieJoueur($idJoueur) {
        $games = $this->findBy(
            ['user'=>$idJoueur]
        );
        return $games;
    }
    public function getTerrain($id) {
        $game = $this->find($id);
        return $game->getTerrain();
    }

    public function findTokens($id,$ressource) {
        $game = $this->find($id);
        $allTokens = $game->getTokens();
        $ressourceTokens = $allTokens[$ressource];
        return $ressourceTokens;
    }
    public function arrayToken($id, $ressource, $data) {
        $game = $this->find($id);
        $allTokens = $game->getTokens();
        $ressourceTokens = $data;
        $allTokens[$ressource] = $ressourceTokens;
        return $allTokens;
    }
}
