<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/06/2016
 * Time: 11:39
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Event;
use Doctrine\ORM\EntityManager;

class EventManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var Event L'événement en cours de traitement */
    private $event;

    public function __construct(EntityManager $doctrine)
    {
        $this->entityManager = $doctrine;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param $token string Le token de l'événement à récupérer
     * @return bool true Si un événement est trouvé
     */
    public function retrieveEvent($token)
    {
        $eventRep = $this->entityManager->getRepository("AppBundle:Event");
        $this->event = $eventRep->findOneBy(array('token' => $token));
        return ($this->event instanceof Event);
    }

}