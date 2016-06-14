<?php

namespace AppBundle\Entity\module;

use AppBundle\Entity\EventInvitation;
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
     * @var EventInvitation
     *
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\EventInvitation")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     *
     */
    private $creator;

    /**
     * Personne ayant payé
     *
     * @var EventInvitation
     *
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\EventInvitation")
     * @ORM\JoinColumn(name="payer_id", referencedColumnName="id")
     */
    private $payer;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\AppBundle\Entity\EventInvitation")
     * @ORM\JoinTable(name="eventinvitation_expenseparticipant",
     *      joinColumns={@ORM\JoinColumn(name="expenseparticipant_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="event_invitation_id", referencedColumnName="id")}
     *      )
     */
    private $listOfParticipants;

    /**
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\module\ExpenseModule", inversedBy="expenseProposals")
     * @ORM\JoinColumn(name="expense_module_id", referencedColumnName="id")
     *
     * @var ExpenseModule
     */
    private $expenseModule;


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
     * @param \AppBundle\Entity\EventInvitation $creator
     *
     * @return ExpenseProposal
     */
    public function setCreator(\AppBundle\Entity\EventInvitation $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \AppBundle\Entity\EventInvitation
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set payer
     *
     * @param \AppBundle\Entity\EventInvitation $payer
     *
     * @return ExpenseProposal
     */
    public function setPayer(\AppBundle\Entity\EventInvitation $payer = null)
    {
        $this->payer = $payer;

        return $this;
    }

    /**
     * Get payer
     *
     * @return \AppBundle\Entity\EventInvitation
     */
    public function getPayer()
    {
        return $this->payer;
    }

    /**
     * Add listOfParticipant
     *
     * @param \AppBundle\Entity\EventInvitation $listOfParticipant
     *
     * @return ExpenseProposal
     */
    public function addListOfParticipant(\AppBundle\Entity\EventInvitation $listOfParticipant)
    {
        $this->listOfParticipants[] = $listOfParticipant;

        return $this;
    }

    /**
     * Remove listOfParticipant
     *
     * @param \AppBundle\Entity\EventInvitation $listOfParticipant
     */
    public function removeListOfParticipant(\AppBundle\Entity\EventInvitation $listOfParticipant)
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

    /**
     * Set expenseModule
     *
     * @param \AppBundle\Entity\module\ExpenseModule $expenseModule
     *
     * @return ExpenseProposal
     */
    public function setExpenseModule(\AppBundle\Entity\module\ExpenseModule $expenseModule = null)
    {
        $this->expenseModule = $expenseModule;

        return $this;
    }

    /**
     * Get expenseModule
     *
     * @return \AppBundle\Entity\module\ExpenseModule
     */
    public function getExpenseModule()
    {
        return $this->expenseModule;
    }
}
