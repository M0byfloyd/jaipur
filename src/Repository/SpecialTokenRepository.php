<?php

namespace App\Repository;

use App\Entity\SpecialToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SpecialToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpecialToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpecialToken[]    findAll()
 * @method SpecialToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpecialTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpecialToken::class);
    }

    public function findArrayById()
    {
        $cards = $this->findAll();
        $t = [];
        foreach ($cards as $card)
        {
            $t[$card->getId()] = $card;
        }
        return $t;
    }

    // /**
    //  * @return SpecialToken[] Returns an array of SpecialToken objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SpecialToken
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}