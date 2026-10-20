<?php

namespace App\Repository;

use App\Entity\Droid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Droid>
 */
class DroidRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Droid::class);
    }

    //    /**
    //     * @return Droid[] Returns an array of Droid objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Droid
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
