<?php

namespace AppBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventOpeningHours
 *
 * @ORM\Table(name="event_opening_hours")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Event\EventOpeningHoursRepository")
 */
class EventOpeningHours
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="dayOfWeek", type="smallint")
     */
    private $dayOfWeek;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timeOpen", type="time")
     */
    private $timeOpen;

    /**
     * @var int
     *
     * @ORM\Column(name="openingDuration", type="integer")
     */
    private $openingDuration;


    /**************************************************************************************************************
     *                                      Jointures
     **************************************************************************************************************/

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\Event", inversedBy="openingHours")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dayOfWeek
     *
     * @param integer $dayOfWeek
     *
     * @return EventOpeningHours
     */
    public function setDayOfWeek($dayOfWeek)
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    /**
     * Get dayOfWeek
     *
     * @return int
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
     * Set timeOpen
     *
     * @param \DateTime $timeOpen
     *
     * @return EventOpeningHours
     */
    public function setTimeOpen($timeOpen)
    {
        $this->timeOpen = $timeOpen;

        return $this;
    }

    /**
     * Get timeOpen
     *
     * @return \DateTime
     */
    public function getTimeOpen()
    {
        return $this->timeOpen;
    }

    /**
     * Set openingDuration
     *
     * @param integer $openingDuration
     *
     * @return EventOpeningHours
     */
    public function setOpeningDuration($openingDuration)
    {
        $this->openingDuration = $openingDuration;

        return $this;
    }

    /**
     * Get openingDuration
     *
     * @return int
     */
    public function getOpeningDuration()
    {
        return $this->openingDuration;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     * @return EventOpeningHours
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }
}

