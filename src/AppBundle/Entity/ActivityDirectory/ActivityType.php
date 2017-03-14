<?php

namespace AppBundle\Entity\ActivityDirectory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 10/03/2017
 * Time: 14:53
 *
 *  ActivityType
 *
 * @ORM\Table(name="activity_directory_activity_type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ActivityDirectory\ActivityTypeRepository")
 */
class ActivityType
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

    /**
     * Contient la clé de traduction
     *
     * @var string
     *
     * @ORM\Column(name="key_name", type="string", length=255, nullable=false)
     */
    private $keyName;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var ArrayCollection
     * @ManyToMany(targetEntity="Activity", mappedBy="activityTypes")
     */
    private $activities;

    /***********************************************************************
     *                      Constructor
     ***********************************************************************/

    public function __construct()
    {
        $this->activities = new ArrayCollection();
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
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * @param string $keyName
     */
    public function setKeyName($keyName)
    {
        $this->keyName = $keyName;
    }

    /**
     * @return Activity
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * @param Activity $activities
     */
    public function setActivities($activities)
    {
        $this->activities = $activities;
    }


}