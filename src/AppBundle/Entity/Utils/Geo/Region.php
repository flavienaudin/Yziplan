<?php

namespace AppBundle\Entity\Utils\Geo;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 10/03/2017
 * Time: 11:05
 *
 *
 * Region
 *
 * @ORM\Table(name="utils_geo_region",indexes={@Index(name="region_name_idx", columns={"name"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Utils\Geo\RegionRepository")
 */
class Region
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Utils\Geo\Department", mappedBy="region", cascade={"persist"})
     */
    private $departments;


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
     * @return ArrayCollection
     */
    public function getDepartments()
    {
        return $this->departments;
    }

    /**
     * @param ArrayCollection $departments
     */
    public function setDepartments($departments)
    {
        $this->departments = $departments;
    }

}