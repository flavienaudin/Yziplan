<?php

namespace AppBundle\Entity\module;

use AppBundle\Entity\AppUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * ExpenseProposal
 *
 * @ORM\Table(name="expense_proposal")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\ExpenseProposalRepository")
 */
class ExpenseProposal
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer", nullable=true)
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expense_date", type="datetime", unique=false, nullable=true)
     */
    private $expenseDate;

    /**
     * @var string
     *
     * @ORM\Column(name="place", type="string", length=255)
     */
    private $place;

    /**
     * @var string
     *
     * @ORM\Column(name="google_place_id", type="string", length=255, nullable=true)
     */
    private $googlePlaceId;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * Personne ayant ajouté la depense
     *
     * @var AppUser
     *
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\AppUser")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     *
     */
    private $creator;

    /**
     * Personne ayant payé
     *
     * @var AppUser
     *
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\AppUser")
     * @ORM\JoinColumn(name="payer_id", referencedColumnName="id")
     */
    private $payer;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\AppBundle\Entity\AppUser")
     * @ORM\JoinTable(name="appuser_expenseparticipant",
     *      joinColumns={@ORM\JoinColumn(name="expenseparticipant_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="appuser_id", referencedColumnName="id")}
     *      )
     */
    private $listOfParticipants;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->listOfParticipants = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ExpenseProposal
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return ExpenseProposal
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     *
     * @return ExpenseProposal
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set expenseDate
     *
     * @param \DateTime $expenseDate
     *
     * @return ExpenseProposal
     */
    public function setExpenseDate($expenseDate)
    {
        $this->expenseDate = $expenseDate;

        return $this;
    }

    /**
     * Get expenseDate
     *
     * @return \DateTime
     */
    public function getExpenseDate()
    {
        return $this->expenseDate;
    }

    /**
     * Set place
     *
     * @param string $place
     *
     * @return ExpenseProposal
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set googlePlaceId
     *
     * @param string $googlePlaceId
     *
     * @return ExpenseProposal
     */
    public function setGooglePlaceId($googlePlaceId)
    {
        $this->googlePlaceId = $googlePlaceId;

        return $this;
    }

    /**
     * Get googlePlaceId
     *
     * @return string
     */
    public function getGooglePlaceId()
    {
        return $this->googlePlaceId;
    }

    /**
     * Set creator
     *
     * @param \AppBundle\Entity\AppUser $creator
     *
     * @return ExpenseProposal
     */
    public function setCreator(\AppBundle\Entity\AppUser $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \AppBundle\Entity\AppUser
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set payer
     *
     * @param \AppBundle\Entity\AppUser $payer
     *
     * @return ExpenseProposal
     */
    public function setPayer(\AppBundle\Entity\AppUser $payer = null)
    {
        $this->payer = $payer;

        return $this;
    }

    /**
     * Get payer
     *
     * @return \AppBundle\Entity\AppUser
     */
    public function getPayer()
    {
        return $this->payer;
    }

    /**
     * Add listOfParticipant
     *
     * @param \AppBundle\Entity\AppUser $listOfParticipant
     *
     * @return ExpenseProposal
     */
    public function addListOfParticipant(\AppBundle\Entity\AppUser $listOfParticipant)
    {
        $this->listOfParticipants[] = $listOfParticipant;

        return $this;
    }

    /**
     * Remove listOfParticipant
     *
     * @param \AppBundle\Entity\AppUser $listOfParticipant
     */
    public function removeListOfParticipant(\AppBundle\Entity\AppUser $listOfParticipant)
    {
        $this->listOfParticipants->removeElement($listOfParticipant);
    }

    /**
     * Get listOfParticipants
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListOfParticipants()
    {
        return $this->listOfParticipants;
    }
}
