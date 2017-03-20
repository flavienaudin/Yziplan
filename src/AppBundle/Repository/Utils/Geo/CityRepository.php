<?php

namespace AppBundle\Repository\Utils\Geo;

use Doctrine\ORM\EntityRepository;

class CityRepository extends EntityRepository
{
    public function getPlaceByStartName($pattern, $maxResult = 10)
    {
        $qb = $this
            ->createQueryBuilder('c');
        $qb
            ->where('c.name LIKE :pattern')
            ->setParameter(':pattern', $pattern.'%')
            ->setMaxResults($maxResult);
        return $qb
            ->getQuery()
            ->getResult();
    }
}
