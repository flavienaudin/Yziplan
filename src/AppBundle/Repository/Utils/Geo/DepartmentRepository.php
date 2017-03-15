<?php

namespace AppBundle\Repository\Utils\Geo;

use Doctrine\ORM\EntityRepository;

class DepartmentRepository extends EntityRepository
{
    public function getPlaceByStartName($pattern, $maxResult = 5)
    {
        $qb = $this
            ->createQueryBuilder('d');
        $qb
            ->where('d.name LIKE :pattern')
            ->setParameter(':pattern', $pattern.'%')
            ->setMaxResults($maxResult);
        return $qb
            ->getQuery()
            ->getResult();
    }
}
