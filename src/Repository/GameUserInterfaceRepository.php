<?php

namespace App\Repository;

use App\Entity\GameUserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GameUserInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method GameUserInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method GameUserInterface[]    findAll()
 * @method GameUserInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameUserInterfaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameUserInterface::class);
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
}
