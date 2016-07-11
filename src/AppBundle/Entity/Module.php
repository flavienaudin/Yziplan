<?php

namespace AppBundle\Entity;

use AppBundle\Entity\module\ExpenseModule;
use AppBundle\Entity\module\PollModule;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Module
 *
 * @ORM\Table(name="module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModuleRepository")
 */
class Module
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=128, unique=true)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="token_edition", type="string", length=128, unique=true)
     */
    private $tokenEdition;

    /**
     * Numéro de placement dans l'ordonnancement des modules d'un événement
     * @var integer
     * @ORM\Column(name="order_index", type="integer", length=3, nullable=true)
     */
    private $orderIndex;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=64)
     */
    private $status;

    /**
     * @var \DateTime
     * @ORM\Column(name="response_deadline", type="datetime", unique=false, nullable=true)
     */
    private $responseDeadline;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="modules")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ModuleInvitation", mappedBy="module")
     */
    private $moduleInvitations;

    /**
     * @ORM\OneToOne(targetEntity="PaymentModule", mappedBy="module")
     *
     * @var PaymentModule
     */
    private $paymentModule;

    /*******************************************************************************************************
     *                                Type de module
     ********************************************************************************************************/

    /**
     * Sondages du module
     *
     * @var ArrayCollection
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\module\PollModule", mappedBy="module", cascade={"persist"})
     */
    private $pollModule;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToOne(targetEntity="\AppBundle\Entity\module\ExpenseModule", mappedBy="module", cascade={"persist"})
     */
    private $expenseModule;

    /*******************************************************************************************************
     *                                Helpers
     ********************************************************************************************************/

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->moduleInvitations = new ArrayCollection();
    }

    /*******************************************************************************************************
     *                                Getters and Setters
     ********************************************************************************************************/

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
     * Set event
     *
     * @param Event $event
     *
     * @return Module
     */
    public function setEvent(Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Add moduleInvitation
     *
     * @param ModuleInvitation $moduleInvitation
     *
     * @return Module
     */
    public function addModuleInvitation(ModuleInvitation $moduleInvitation)
    {
        $this->moduleInvitations[] = $moduleInvitation;

        return $this;
    }

    /**
     * Remove moduleInvitation
     *
     * @param ModuleInvitation $moduleInvitation
     */
    public function removeModuleInvitation(ModuleInvitation $moduleInvitation)
    {
        $this->moduleInvitations->removeElement($moduleInvitation);
    }

    /**
     * Get moduleInvitations
     *
     * @return ArrayCollection
     */
    public function getModuleInvitations()
    {
        return $this->moduleInvitations;
    }

    /**
     * Set paymentModule
     *
     * @param PaymentModule $paymentModule
     *
     * @return Module
     */
    public function setPaymentModule(PaymentModule $paymentModule = null)
    {
        $this->paymentModule = $paymentModule;

        return $this;
    }

    /**
     * Get paymentModule
     *
     * @return PaymentModule
     */
    public function getPaymentModule()
    {
        return $this->paymentModule;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Module
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
     * @return Module
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
     * Set token
     *
     * @param string $token
     *
     * @return Module
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set tokenEdition
     *
     * @param string $tokenEdition
     *
     * @return Module
     */
    public function setTokenEdition($tokenEdition)
    {
        $this->tokenEdition = $tokenEdition;

        return $this;
    }

    /**
     * Get tokenEdition
     *
     * @return string
     */
    public function getTokenEdition()
    {
        return $this->tokenEdition;
    }

    /**
     * @return mixed
     */
    public function getOrderIndex()
    {
        return $this->orderIndex;
    }

    /**
     * @param mixed $orderIndex
     * @return Module
     */
    public function setOrderIndex($orderIndex)
    {
        $this->orderIndex = $orderIndex;
        return $this;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Module
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set responseDeadline
     *
     * @param \DateTime $responseDeadline
     *
     * @return Module
     */
    public function setResponseDeadline($responseDeadline)
    {
        $this->responseDeadline = $responseDeadline;

        return $this;
    }

    /**
     * Get responseDeadline
     *
     * @return \DateTime
     */
    public function getResponseDeadline()
    {
        return $this->responseDeadline;
    }

    /**
     * Set pollModule
     *
     * @param PollModule $pollModule
     *
     * @return Module
     */
    public function setPollModule(PollModule $pollModule)
    {
        $this->pollModule = $pollModule;
        $this->pollModule->setModule($this);

        return $this;
    }

    /**
     * Get pollModule
     *
     * @return PollModule
     */
    public function getPollModule()
    {
        return $this->pollModule;
    }

    /**
     * Set expenseModule
     *
     * @param ExpenseModule $expenseModule
     *
     * @return Module
     */
    public function setExpenseModule(ExpenseModule $expenseModule)
    {
        $this->expenseModule = $expenseModule;
        $expenseModule->setModule($this);

        return $this;
    }

    /**
     * Get expenseModule
     *
     * @return ExpenseModule
     */
    public function getExpenseModule()
    {
        return $this->expenseModule;
    }
}
