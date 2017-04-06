<?php

namespace AppBundle\Repository\ActivityDirectory;

use Doctrine\ORM\EntityRepository;

class ActivityRepository extends EntityRepository
{
    /**
     * Retourne les x derniers ajout dans laliste des activités
     *
     * @param int $number
     * @return array de Activity
     */
    public function getLastAddedActivities($number = 10)
    {
        $qb = $this
            ->createQueryBuilder('a');
        $qb
            ->orderBy('a.id', 'DESC')
            ->setMaxResults($number);
        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la liste des activités en fonction d'une liste de lieu et de type d'activité
     *
     * @param array $types liste d'id d'ActivityType
     * @param array $place liste de nom de lieu
     *
     * @param null $cp
     * @return array de Activity
     */
    public function getActivityByTypeAndPlace($types, $place, $cp = null)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->leftJoin('a.event', 'e')
            ->where('e.duplicationEnabled = 1');
        if (!empty($types) && !empty($types[0])) {
            $qb->leftJoin('a.activityTypes', 'at')
                ->andWhere('at.id in (:types)')
                ->setParameter(':types', $types);
        }
        if (!empty($place)) {
            $qb->leftJoin('a.cities', 'c')
                ->leftJoin('c.department', 'd')
                ->leftJoin('d.region', 'r')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like('c.name', ':place'),
                    $qb->expr()->like('d.name', ':place'),
                    $qb->expr()->like('r.name', ':place')
                ))
                ->setParameter(':place', $place . '%');
            if (!empty($cp)) {
                $qb->andWhere('c.postal_code = :cp')
                    ->setParameter(':cp', $cp);
            }
        }
        return $qb
            ->getQuery()
            ->getResult();
    }
}
