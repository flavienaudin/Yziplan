<?php

namespace AppBundle\Entity\Event;

use AppBundle\Entity\Notifications\EventInvitationPreferences;
use AppBundle\Entity\Notifications\Notification;
use AppBundle\Entity\Payment\Wallet;
use AppBundle\Entity\User\ApplicationUser;
use AppBundle\Utils\enum\EventInvitationAnswer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;


/**
 * EventInvitation
 *
 * @ORM\Table(name="event_event_invitation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Event\EventInvitationRepository")
 */
class EventInvitation
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
     * @ORM\Column(name="token", type="string", length=128, unique=true)
     */
    private $token;

    /**
     * @var string
     * @ORM\Column(name="status", type="enum_eventinvitation_status")
     */
    private $status;

    /**
     * L'état "archived=true" permet entre autre à l'utilisateur de trier ses événements dans la liste de ses invitations
     *
     * @var boolean
     * @ORM\Column(name="archived", type="boolean")
     */
    private $archived = false;

    /**
     * @var string
     *
     * @ORM\Column(name="guest_name", type="string", length=255, nullable=true)
     */
    private $guestName;

    /**
     * Cf. AppBundle/Utils/enum/EventInvitationAnswer
     * @var string
     *
     * @ORM\Column(name="answer", type="enum_eventinvitation_answer", length=128, nullable=false)
     */
    private $answer = EventInvitationAnswer::DONT_KNOW;

    /**
     * @var bool
     * @ORM\Column(name="creator", type="boolean")
     */
    private $creator = false;

    /**
     * @var bool
     * @ORM\Column(name="administrator", type="boolean")
     */
    private $administrator = false;

    /**
     * @var \DateTime
     * @ORM\Column(name="invitation_email_sent_at", type="datetime", nullable=true)
     */
    private $invitationEmailSentAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_visit_at", type="datetime", nullable=true)
     */
    private $lastVisitAt;

    /**************************************************************************************************************
     *                                      Jointures
     **************************************************************************************************************/

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\Event", inversedBy="eventInvitations")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var ApplicationUser
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User\ApplicationUser", inversedBy="eventInvitations")
     * @ORM\JoinColumn(name="application_user_id", referencedColumnName="id")
     */
    private $applicationUser;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Event\ModuleInvitation", mappedBy="eventInvitation", cascade={"persist"} )
     */
    private $moduleInvitations;

    /**
     * @var Wallet
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Payment\Wallet", mappedBy="eventInvitation")
     */
    private $wallet;

    /**
     * @var ArrayCollection Ensemble des notifications à afficher pour cette invitation
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Notifications\Notification", mappedBy="eventInvitation")
     */
    private $notifications;

    /**
     * @var EventInvitationPreferences
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Notifications\EventInvitationPreferences", inversedBy="eventInvitation", cascade={"persist"})
     * @ORM\JoinColumn(name="event_invitation_preferences", referencedColumnName="id", nullable=true)
     */
    private $eventInvitationPreferences;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->moduleInvitations = new ArrayCollection();
        $this->notifications = new ArrayCollection();
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
    public function getGuestName()
    {
        return $this->guestName;
    }

    /**
     * @param string $guestName
     */
    public function setGuestName($guestName)
    {
        $this->guestName = $guestName;
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
     * @return EventInvitation
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @param bool $archived
     * @return EventInvitation
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;
        return $this;
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
     * @return EventInvitation
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param string|null $answer
     * @return EventInvitation
     */
    public function setAnswer($answer = null)
    {
        $this->answer = $answer;
        return $this;
    }

    /**
     * Get event
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set event
     * @param Event $event
     * @return EventInvitation
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCreator()
    {
        return $this->creator;
    }

    /**
     * @param boolean $creator
     * @return EventInvitation
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAdministrator()
    {
        return $this->administrator;
    }

    /**
     * @param boolean $administrator
     * @return EventInvitation
     */
    public function setAdministrator($administrator)
    {
        $this->administrator = $administrator;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getInvitationEmailSentAt()
    {
        return $this->invitationEmailSentAt;
    }

    /**
     * @param \DateTime $invitationEmailSentAt
     * @return EventInvitation
     */
    public function setInvitationEmailSentAt($invitationEmailSentAt)
    {
        $this->invitationEmailSentAt = $invitationEmailSentAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastVisitAt()
    {
        return $this->lastVisitAt;
    }

    /**
     * @param \DateTime $lastVisitAt
     * @return EventInvitation
     */
    public function setLastVisitAt($lastVisitAt)
    {
        $this->lastVisitAt = $lastVisitAt;
        return $this;
    }

    /**
     * Get applicationUser
     *
     * @return ApplicationUser
     */
    public function getApplicationUser()
    {
        return $this->applicationUser;
    }

    /**
     * Set applicationUser
     *
     * @param ApplicationUser $applicationUser
     *
     * @return EventInvitation
     */
    public function setApplicationUser(ApplicationUser $applicationUser = null)
    {
        $this->applicationUser = $applicationUser;
        return $this;
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
     *
     * @param ModuleInvitation $moduleInvitation
     *
     * @return EventInvitation
     */
    public function addModuleInvitation(ModuleInvitation $moduleInvitation)
    {
        $this->moduleInvitations[] = $moduleInvitation;
        $moduleInvitation->setEventInvitation($this);

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
     * @return ArrayCollection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Add Notification
     *
     * @param Notification $notification
     * @return EventInvitation
     */
    public function addNotification(Notification $notification)
    {
        $this->notifications[] = $notification;
        $notification->setEventInvitation($this);

        return $this;
    }

    /**
     * Remove Notification
     * @param Notification $notification
     */
    public function removeNotification(Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Remove Notification
     * @param Notification $notification
     */
    public function removeAllNotifications()
    {
        $this->notifications->clear();
    }

    /**
     * @return mixed
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * @param mixed $wallet
     * @return EventInvitation
     */
    public function setWallet(Wallet $wallet)
    {
        $this->wallet = $wallet;
        return $this;
    }

    /**
     * @return EventInvitationPreferences
     */
    public function getEventInvitationPreferences()
    {
        if($this->eventInvitationPreferences == null){
            $this->setEventInvitationPreferences(new EventInvitationPreferences());
        }
        return $this->eventInvitationPreferences;
    }

    /**
     * @param EventInvitationPreferences $eventInvitationPreferences
     * @return EventInvitation
     */
    public function setEventInvitationPreferences($eventInvitationPreferences)
    {
        $this->eventInvitationPreferences = $eventInvitationPreferences;
        return $this;
    }


    /***********************************************************************
     *                      Helpers
     ***********************************************************************/
    /**
     * Retourne le nom de l'invité à afficher en fonction des données renseignées et de l'utilisateur associé.
     * @return string
     */
    public function getDisplayableName($useEmail = false, $obfuscateEmail = true)
    {
        $displayableName = $this->guestName;
        if (empty($displayableName)) {
            if ($this->getApplicationUser() != null) {
                $displayableName = $this->getApplicationUser()->getDisplayableName($useEmail, $obfuscateEmail);
            }
        }
        return $displayableName;
    }

    /**
     * Retourne l'email de l'invité à afficher en fonction de l'utilisateur associé.
     * @return string
     */
    public function getDisplayableEmail($appliedRot13 = false)
    {
        $displayableEmail = null;
        if ($this->getApplicationUser() != null) {
            if ($this->getApplicationUser()->getAccountUser() == null && $this->getApplicationUser()->getAppUserEmails()->count() > 0) {
                $displayableEmail = $this->getApplicationUser()->getAppUserEmails()->first()->getEmail();
            } elseif ($this->getApplicationUser()->getAccountUser() != null) {
                $displayableEmail = $this->getApplicationUser()->getAccountUser()->getEmail();
            }
        }
        if($appliedRot13){
            $displayableEmail = str_rot13($displayableEmail);
        }
        return $displayableEmail;
    }

    /**
     * Indique si l'invité est un organisateur : Créateur ou administrateur
     * @return bool
     */
    public function isOrganizer()
    {
        return $this->isCreator() || $this->isAdministrator();
    }


    /**
     * Get moduleInvitation for the given module
     *
     * @return ModuleInvitation
     */
    public function getModuleInvitationForModule(Module $module)
    {
        /** @var ModuleInvitation $moduleInvitation */
        foreach ($this->getModuleInvitations() as $moduleInvitation) {
            if ($moduleInvitation->getModule()->getId() == $module->getId()) {
                return $moduleInvitation;
            }
        }
        return null;
    }

    /**
     * @return array Les notifications triées par date
     */
    public function getSortedNotifications($order = 'Asc')
    {
        $notifications = $this->notifications->toArray();
        uasort($notifications, array(Notification::class, 'compare' . $order));
        return $notifications;
    }

}
