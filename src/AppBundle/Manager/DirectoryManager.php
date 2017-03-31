<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 10/03/2017
 * Time: 17:12
 */

namespace AppBundle\Manager;

use AppBundle\Entity\ActivityDirectory\Activity;
use Doctrine\ORM\EntityManager;

class DirectoryManager
{

    /** @var EntityManager */
    private $entityManager;


    public function __construct(EntityManager $doctrine)
    {
        $this->entityManager = $doctrine;
    }

    public function getActivities($number = 15)
    {
        return $this->entityManager->getRepository(Activity::class)->getLastAddedActivities(15);
    }

    public function searchActivities($types, $place)
    {
        $place = explode(' (', $place)[0];
        return $this->entityManager->getRepository(Activity::class)->getActivityByTypeAndPlace($types, $place );
    }

}