<?php

namespace AppBundle\Entity\Utils\Geo;

use AppBundle\Entity\ActivityDirectory\Activity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\ManyToMany;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 10/03/2017
 * Time: 11:01
 *
 * City
 *
 * @ORM\Table(name="utils_geo_city",indexes={@Index(name="city_name_idx", columns={"name"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Utils\Geo\CityRepository")
 */
class City
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
     * @var int
     *
     * @ORM\Column(name="postal_code", type="integer", nullable=false)
     */
    private $postal_code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="insee_code", type="integer", nullable=true)
     */
    private $insee_code;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float", nullable=true)
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float", nullable=true)
     */
    private $longitude;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var ArrayCollection
     * @ManyToMany(targetEntity="AppBundle\Entity\ActivityDirectory\Activity", mappedBy="cities")
     */
    private $activities;

    /**
     * @var Department
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Utils\Geo\Department", inversedBy="cities")
     * @ORM\JoinColumn(name="department_id", referencedColumnName="id")
     */
    private $department;

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
     * @return int
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param int $postal_code
     */
    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getInseeCode()
    {
        return $this->insee_code;
    }

    /**
     * @param int $insee_code
     */
    public function setInseeCode($insee_code)
    {
        $this->insee_code = $insee_code;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param Department $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
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