<?php

namespace AppBundle\Repository\Utils\Geo;

use Doctrine\ORM\EntityRepository;

class RegionRepository extends EntityRepository
{
    public function getPlaceByStartName($pattern, $maxResult = 5)
    {
        $qb = $this
            ->createQueryBuilder('r');
        $qb
            ->where('r.name LIKE :pattern')
            ->setParameter(':pattern', $pattern.'%')
            ->setMaxResults($maxResult);
        return $qb
            ->getQuery()
            ->getResult();
    }
}
