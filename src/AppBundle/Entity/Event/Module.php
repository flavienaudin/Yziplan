<?php

namespace AppBundle\Entity\Event;

use AppBundle\Entity\Module\ExpenseModule;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Payment\PaymentModule;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Module
 *
 * @ORM\Table(name="event_module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Event\ModuleRepository")
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
     * Numéro de placement dans l'ordonnancement des modules d'un événement
     * @var integer
     * @ORM\Column(name="order_index", type="integer", length=3, nullable=true)
     */
    private $orderIndex;

    /**
     * @var string
     * @ORM\Column(name="status", type="enum_module_status")
     */
    private $status;

    /**
     * @var \DateTime
     * @ORM\Column(name="response_deadline", type="datetime", unique=false, nullable=true)
     */
    private $responseDeadline;

    /**
     * If "true" then only guest with invitation can answer. No moduleInvitation creation when displaying the module.
     * If "null" then it inherits the Event.invitationOnly
     * @var boolean*
     * @ORM\Column(name="invitation_only", type="boolean", nullable=true)
     */
    private $invitationOnly = null;

    /**
     * If "true" then guests can send invitation to others.
     * @var boolean
     * @ORM\Column(name="guests_can_invite", type="boolean")
     */
    private $guestsCanInvite = true;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\Event", inversedBy="modules")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Event\ModuleInvitation", mappedBy="module")
     */
    private $moduleInvitations;

    /**
     * @var PaymentModule
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Payment\PaymentModule", mappedBy="module")
     */
    private $paymentModule;


    /***********************************************************************
     *                                Type de module
     ***********************************************************************/

    /**
     * Module de type sondage
     * @var PollModule
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Module\PollModule", mappedBy="module", cascade={"persist"})
     */
    private $pollModule;

    /**
     * Module de type CompteEntreAmis
     * @var ExpenseModule
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Module\ExpenseModule", mappedBy="module", cascade={"persist"})
     */
    private $expenseModule;

    /**
     * Module de type Cagnotte
     * @ var PoolModule     *
     * @ ORM\OneToOne(targetEntity="AppBundle\Entity\Module\ExpenseModule", mappedBy="module", cascade={"persist"})
     */
    // TOTO private $poolModule;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return int
     */
    public function getOrderIndex()
    {
        return $this->orderIndex;
    }

    /**
     * @param int $orderIndex
     */
    public function setOrderIndex($orderIndex)
    {
        $this->orderIndex = $orderIndex;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getResponseDeadline()
    {
        return $this->responseDeadline;
    }

    /**
     * @param \DateTime $responseDeadline
     */
    public function setResponseDeadline($responseDeadline)
    {
        $this->responseDeadline = $responseDeadline;
    }

    /**
     * @return boolean
     */
    public function isInvitationOnly()
    {
        return $this->invitationOnly;
    }

    /**
     * @param boolean $invitationOnly
     */
    public function setInvitationOnly($invitationOnly)
    {
        $this->invitationOnly = $invitationOnly;
    }

    /**
     * @return boolean
     */
    public function isGuestsCanInvite()
    {
        return $this->guestsCanInvite;
    }

    /**
     * @param boolean $guestsCanInvite
     */
    public function setGuestsCanInvite($guestsCanInvite)
    {
        $this->guestsCanInvite = $guestsCanInvite;
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
     */
    public function setEvent($event)
    {
        $this->event = $event;
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
     * Add moduleInvitation
     * @param ModuleInvitation $moduleInvitation
     * @return Module
     */
    public function addModuleInvitation(ModuleInvitation $moduleInvitation)
    {
        $this->moduleInvitations[] = $moduleInvitation;
        $moduleInvitation->setModule($this);

        return $this;
    }

    /**
     * Remove moduleInvitation
     * @param ModuleInvitation $moduleInvitation
     */
    public function removeModuleInvitation(ModuleInvitation $moduleInvitation)
    {
        $this->moduleInvitations->removeElement($moduleInvitation);
    }

    /**
     * Get paymentModule
     * @return PaymentModule
     */
    public function getPaymentModule()
    {
        return $this->paymentModule;
    }

    /**
     * @param PaymentModule $paymentModule
     * @return Module
     */
    public function setPaymentModule(PaymentModule $paymentModule = null)
    {
        $this->paymentModule = $paymentModule;

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
     * Get expenseModule
     *
     * @return ExpenseModule
     */
    public function getExpenseModule()
    {
        return $this->expenseModule;
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

    /***********************************************************************
     *                      Helpers
     ***********************************************************************/

    /**
     * Retrieve EventInvitation with creator = true
     * @return Collection of EventInvitation
     */
    public function getCreators()
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("creator", true));
        return $this->moduleInvitations->matching($criteria);
    }

    /**
     * Retrieve EventInvitation with administrator = true
     * @return Collection
     */
    public function getAdministrators()
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("administrator", true));
        return $this->moduleInvitations->matching($criteria);
    }

    /**
     * @param int $maxResult
     * @param array $excludedModuleInvitations
     * @return Collection
     */
    public function getFilteredModuleInvitations($maxResult = 0, $excludedModuleInvitations = array())
    {

        $criteria = Criteria::create()->where(Criteria::expr()->eq("status", ModuleInvitationStatus::VALID));

        if ($excludedModuleInvitations != null) {
            $excludedModuleInvitationsId = array();
            foreach ($excludedModuleInvitations as $moduleInvitation) {
                if ($moduleInvitation instanceof ModuleInvitation) {
                    $excludedModuleInvitationsId[] = $moduleInvitation->getId();
                }
            }
            $criteria->andWhere(Criteria::expr()->notIn('id', $excludedModuleInvitationsId));
        }

        if ($maxResult > 0) {
            $criteria->setMaxResults($maxResult);
        }
        return $this->moduleInvitations->matching($criteria);
    }
}
