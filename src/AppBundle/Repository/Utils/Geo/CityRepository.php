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
            ->setParameter(':pattern', $pattern . '%')
            ->setMaxResults($maxResult);
        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getCityByNameAndCP($place, $cp)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.name = :place')
            ->andWhere('c.postal_code = :cp')
            ->setParameter(':place', $place)
            ->setParameter(':cp', $cp)
            ->setMaxResults( 1 );
        return $qb
            ->getQuery()
            ->getResult();
    }
}
