<?php

namespace App\Repository;

use App\Entity\StarshipPart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StarshipPart>
 */
class StarshipPartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StarshipPart::class);
    }

    public static function createExpensiveCriteria(): Criteria
    {
        return Criteria::create()->andWhere(Criteria::expr()->gt('price', 50000));
    }

    /**
     * @return Collection<StarshipPart>
     */
    public function getExpensiveParts(int $limit = 10): Collection
    {
        return $this->createQueryBuilder('sp')
            ->addCriteria(self::createExpensiveCriteria())
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<StarshipPart>
     */
    public function findAllOrderedByPrice(string $search = ''): array
    {
        $qb = $this->createQueryBuilder('sp')
            ->orderBy('sp.price', 'DESC')
            ->innerJoin('sp.starship', 's')
            ->addSelect('s')
        ;

        if ($search) {
            $qb->andWhere('LOWER(sp.name) LIKE :search OR LOWER(sp.notes) LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb->getQuery()
            ->getResult();
    }
}
