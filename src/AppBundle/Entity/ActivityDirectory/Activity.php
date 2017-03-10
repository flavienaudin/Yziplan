<?php

namespace AppBundle\Entity\ActivityDirectory;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 10/03/2017
 * Time: 16:15
 *
 * Activity
 *
 * @ORM\Table(name="activity_directory_activity")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ActivityDirectory\ActivityRepository")
 */
class Activity
{
    /** Active les timestamps automatiques pour la creation et la mise a jour */
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var \AppBundle\Entity\Event\Event
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var \AppBundle\Entity\Utils\Geo\City
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Utils\Geo\City", inversedBy="activities")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    private $city;

    /**
     * @var ActivityType
     * @ManyToMany(targetEntity="AppBundle\Entity\ActivityDirectory\ActivityType", inversedBy="activities")
     * @JoinTable(name="activity_activity_type")
     */
    private $activityTypes;

    /***********************************************************************
     *                      Constructor
     ***********************************************************************/

    public function __construct()
    {
        $this->activityTypes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \AppBundle\Entity\Utils\Geo\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param \AppBundle\Entity\Utils\Geo\City $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return ActivityType
     */
    public function getActivityTypes()
    {
        return $this->activityTypes;
    }

    /**
     * @param ActivityType $activityTypes
     */
    public function setActivityTypes($activityTypes)
    {
        $this->activityTypes = $activityTypes;
    }

    /**
     * @return \AppBundle\Entity\Event\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param \AppBundle\Entity\Event\Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }


}