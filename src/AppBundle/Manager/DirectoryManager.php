<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 10/03/2017
 * Time: 17:12
 */

namespace AppBundle\Manager;

use AppBundle\Entity\ActivityDirectory\Activity;
use AppBundle\Entity\ActivityDirectory\ActivityType;
use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Utils\Geo\City;
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
        return $this->entityManager->getRepository(Activity::class)->getLastAddedActivities($number);
    }

    /**
     * @param $types
     * @param $place
     * @return array
     */
    public function searchActivities($types, $place)
    {
        $placeData = explode(' (', $place);
        $place = $placeData[0];
        $cp = null;
        if (isset ($placeData[1])) {
            $cp = substr($placeData[1], strlen($placeData[1]) - 2);// on enlève le ')' en fin de chaine
        }
        return $this->entityManager->getRepository(Activity::class)->getActivityByTypeAndPlace($types, $place, $cp);
    }

    /**
     * @param $eventId
     * @param $typeIds
     * @param $place
     */
    public function treatAddActivityForm($eventId, $typeIds, $place)
    {
        $activity = new Activity();
        // Set event
        $event = $this->entityManager->getRepository(Event::class)->findOneBy(array('id' => $eventId));
        // Set activity type
        $activity->setEvent($event);
        foreach ($typeIds as $typeId) {
            $activityType = $this->entityManager->getRepository(ActivityType::class)->findOneBy(array('id' => $typeId));
            $activity->addActivityType($activityType);
        }
        // Set city type
        $placeData = explode(' (', $place);
        if (isset ($placeData[1])) {
            $place = $placeData[0];
            $cp = null;
            $cp = substr($placeData[1],0, strlen($placeData[1]) - 1);// on enlève le ')' en fin de chaine
            $city = $this->entityManager->getRepository(City::class)->getCityByNameAndCP($place, $cp)[0];
            $activity->addCity($city);
        }  else {
            return null; // oui c'est moche
        }

        $this->entityManager->persist($activity);
        $this->entityManager->flush();

    }

}