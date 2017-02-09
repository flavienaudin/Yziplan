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
     * @ORM\Column(name="dayOfWeek", type="enum_day_of_week")
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
     * @ORM\Column(name="time_closed", type="time")
     */
    private $timeClosed;


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
     * @return int
     */
    public function getTimeClosed()
    {
        return $this->timeClosed;
    }

    /**
     * @param int $timeClosed
     * @return EventOpeningHours
     */
    public function setTimeClosed($timeClosed)
    {
        $this->timeClosed = $timeClosed;
        return $this;
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

