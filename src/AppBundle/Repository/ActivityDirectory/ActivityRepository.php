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
     * @param array $whats liste d'id d'ActivityType
     * @param array $whereCities liste d'id de City
     * @param array $whereDepartments liste d'id de Department
     * @param array $whereRegions liste d'id de Region
     *
     * @return array de Activity
     */
    public function getActivityByTypeAndPlace($whats, $whereCities, $whereDepartments, $whereRegions)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->leftJoin('a.event', 'e')
            ->where('e.duplicationEnabled = 1');
        if (!empty($whats)) {
            $qb->leftJoin('a.activityTypes', 'at')
                ->andWhere('at.activities in :whats')
                ->setParameter(':whats', $whats);
        }
        if (!empty($whereCities) || !empty($whereDepartments) || !empty($whereRegions)) {
            $qb->leftJoin('a.cities', 'c')
                ->leftJoin('c.department', 'd')
                ->leftJoin('d.region', 'r')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->in('c.id', ':whereCities'),
                    $qb->expr()->in('d.id', ':whereDepartments'),
                    $qb->expr()->in('r.id', ':whereRegions')
                ))
                ->setParameter(':whereCities', $whereCities)
                ->setParameter(':whereDepartments', $whereDepartments)
                ->setParameter(':whereRegions', $whereRegions);
        }
        return $qb
            ->getQuery()
            ->getResult();
    }
}
