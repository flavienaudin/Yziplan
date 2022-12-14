<?php

namespace AppBundle\Entity\Event;

use AppBundle\Entity\Comment\CommentableInterface;
use AppBundle\Entity\Comment\Thread;
use AppBundle\Entity\Module\ExpenseModule;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Payment\PaymentModule;
use AppBundle\Utils\enum\InvitationRule;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Model\ThreadInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Module
 *
 * @ORM\Table(name="event_module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Event\ModuleRepository")
 */
class Module implements CommentableInterface
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
     * Règle de gestion des invitations : Tout le monde, Tout le monde sauf, Personne sauf
     * @var string
     * @ORM\Column(name="invitation_rule", type="enum_invitation_rule")
     */
    private $invitationRule = InvitationRule::EVERYONE;

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
     * @var Thread
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Comment\Thread")
     * @ORM\JoinColumn(name="comment_thread_id", referencedColumnName="id", nullable=true)
     */
    private $commentThread;

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
     * @return mixed
     */
    public function getInvitationRule()
    {
        return $this->invitationRule;
    }

    /**
     * @param mixed $invitationRule
     * @return Module
     */
    public function setInvitationRule($invitationRule)
    {
        $this->invitationRule = $invitationRule;
        return $this;
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
     * @return Thread
     */
    public function getCommentThread()
    {
        return $this->commentThread;
    }

    /**
     * @param ThreadInterface $commentThread
     * @return Module
     */
    public function setCommentThread($commentThread)
    {
        $this->commentThread = $commentThread;
        return $this;

    }

    /**
     * @return string The Id of the thread to create or get
     */
    public function getThreadId()
    {
        return $this->event->getToken() . '_' . $this->getToken();
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
     * Retrieve EventInvitation with creator = true or administrator = true
     * @return Collection of EventInvitation
     */
    public function getOrganizers()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("status", ModuleInvitationStatus::INVITED))
            ->andWhere(Criteria::expr()->orX(
                Criteria::expr()->eq("creator", true),
                Criteria::expr()->eq("administrator", true)
            ));
        return $this->moduleInvitations->matching($criteria);
    }

    /**
     * @param int $maxResult
     * @param array $excludedModuleInvitations
     * @return Collection
     */
    public function getFilteredModuleInvitations($maxResult = 0, $excludedModuleInvitations = array())
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("status", ModuleInvitationStatus::INVITED));
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
