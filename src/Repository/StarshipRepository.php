<?php

namespace App\Repository;

use App\Entity\Starship;
use App\Entity\StarshipStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @extends ServiceEntityRepository<Starship>
 */
class StarshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Starship::class);
    }

    /**
     * @return Starship[]
     */
    public function findIncomplete(): Pagerfanta
    {
        $query = $this->createQueryBuilder('s')
            ->where('s.status != :status')
            ->orderBy('s.arrivedAt', 'DESC')
            ->setParameter('status', StarshipStatusEnum::COMPLETED)
            ->getQuery()
        ;

        return new Pagerfanta(new QueryAdapter($query));
    }

    public function findMyShip(): Starship
    {
        return $this->findAll()[0];
    }

    //    /**
    //     * @return Starship[] Returns an array of Starship objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Starship
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
