<?php

namespace AppBundle\Entity\Module;

use AppBundle\Entity\Event\Module;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Intl;

/**
 * KittyModule
 *
 * @ORM\Table(name="module_kitty_module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Module\KittyModuleRepository")
 */
class KittyModule
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
     * @var string $currency The 3-letter ISO 4217 currency code indicating the currency to use
     * @ORM\Column(name="currency", type="string")
     */
    private $currency = 'EUR';

    /**
     * @var float
     * @ORM\Column(name="total_amount", type="float")
     */
    private $totalAmount = 0;

    /**
     * Le montant à atteindre
     * @var float
     * @ORM\Column(name="objective_amount", type="float", nullable=true)
     */
    private $objectiveAmount;

    /**
     * @var
     * @ORM\Column(name="objective_type", type="enum_kittymodule_objectivetype", nullable=true)
     */
    private $objectiveType;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var Module
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Event\Module", inversedBy="kittyModule")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id")
     */
    private $module;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Module\KittyParticipation", mappedBy="kittyModule", cascade={"persist"})
     */
    private $participations;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->participations = new ArrayCollection();
    }

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
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return KittyModule
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param float $totalAmount
     * @return KittyModule
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getObjectiveAmount()
    {
        return $this->objectiveAmount;
    }

    /**
     * @param mixed $objectiveAmount
     * @return KittyModule
     */
    public function setObjectiveAmount($objectiveAmount)
    {
        $this->objectiveAmount = $objectiveAmount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getObjectiveType()
    {
        return $this->objectiveType;
    }

    /**
     * @param mixed $objectiveType
     * @return KittyModule
     */
    public function setObjectiveType($objectiveType)
    {
        $this->objectiveType = $objectiveType;
        return $this;
    }

    /**
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param Module $module
     * @return KittyModule
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }


    /**
     * Get participations
     *
     * @return ArrayCollection
     */
    public function getParticipations()
    {
        return $this->participations;
    }

    /**
     * Add participation
     * @param KittyParticipation $participation
     * @return KittyModule
     */
    public function addKittyParticipation(KittyParticipation $participation)
    {
        $this->participations[] = $participation;
        $participation->setKittyModule($this);
        return $this;
    }

    /**
     * Remove participation
     * @param KittyParticipation $participation
     */
    public function removeKittyParticipation(KittyParticipation $participation)
    {
        $this->participations->removeElement($participation);
    }
}

