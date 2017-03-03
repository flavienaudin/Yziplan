<?php

namespace AppBundle\Repository\Event;

use AppBundle\Entity\User\ApplicationUser;
use AppBundle\Utils\enum\EventStatus;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * EventInvitationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EventInvitationRepository extends EntityRepository
{

    public function getUpcomingEventInvitationsByApplicationUser(ApplicationUser $applicationUser)
    {
        $qb = $this
            ->createQueryBuilder('ei');
        $qb
            ->join('ei.event', 'e', Expr\Join::ON)
            ->where('ei.applicationUser = :application_user')
            ->andWhere('ei.archived = 0')
            ->andWhere('e.status != :deprogrammededStatus')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('e.when'),
                $qb->expr()->gt('e.when', ':datelimit')
            )
            )
            ->orderBy('e.when', 'desc')
            ->setParameter(':deprogrammededStatus', EventStatus::DEPROGRAMMED)
            ->setParameter(':application_user', $applicationUser)
            ->setParameter(':datelimit', new  \DateTime());
        return $qb
            ->getQuery()
            ->getResult();
    }


    public function getPassedArchivedEventInvitationsByApplicationUser(ApplicationUser $applicationUser)
    {
        $qb = $this
            ->createQueryBuilder('ei');
        $qb
            ->join('ei.event', 'e', Expr\Join::ON)
            ->where('ei.applicationUser = :application_user')
            ->andWhere('e.status != :deprogrammededStatus')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('ei.archived',true),
                $qb->expr()->lt('e.when', ':datelimit')
            ))
            ->orderBy('e.when', 'desc')
            ->setParameter(':deprogrammededStatus', EventStatus::DEPROGRAMMED)
            ->setParameter(':application_user', $applicationUser)
            ->setParameter(':datelimit', new  \DateTime());
        return $qb
            ->getQuery()
            ->getResult();
    }
}