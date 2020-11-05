<?php

namespace App\Repository;

use App\Entity\PlayUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlayUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayUser[]    findAll()
 * @method PlayUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayUser::class);
    }

    // /**
    //  * @return GameUser[] Returns an array of GameUser objects
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
    public function findOneBySomeField($value): ?GameUser
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function partieJoueur($user) {
        $games = $this->findBy(
            ['user'=>$user]
        );
        return $games;
    }
    public function findPlayUser($game_id, $joueur_id)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from($this->_entityName, 'u')
            ->where('u.Game = :game_id')
            ->andWhere('u.user = :joueur_id')
            ->setParameters(['game_id'=> $game_id,'joueur_id'=>$joueur_id]);
        return $qb->getQuery()->getResult();

    }
}
