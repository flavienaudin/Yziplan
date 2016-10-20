<?php

namespace AppBundle\Entity\Event;

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
     *
     * @ORM\Column(name="token", type="string", length=128, unique=true)
     */
    private $token;

    /**
     * @var string
     * @ORM\Column(name="status", type="enum_eventinvitation_status")
     */
    private $status;

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


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->moduleInvitations = new ArrayCollection();
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
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set event
     *
     * @param Event $event
     *
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


    /***********************************************************************
     *                      Helpers
     ***********************************************************************/
    /**
     * Retourne le nom de l'invité à afficher en fonction des données renseignées et de l'utilisateur associé.
     * @return string
     */
    public function getDisplayableName()
    {
        $displayableName = $this->guestName;
        if (empty($displayableName)) {
            if ($this->getApplicationUser() != null) {
                $displayableName = $this->getApplicationUser()->getDisplayableName();
            }
        }
        return $displayableName;
    }

    /**
     * Retourne l'email de l'invité à afficher en fonction de l'utilisateur associé.
     * @return string
     */
    public function getDisplayableEmail()
    {
        $displayableEmail = null;
        if ($this->getApplicationUser() != null) {
            if ($this->getApplicationUser()->getAppUserEmails()->count() > 0) {
                $this->getApplicationUser()->getAppUserEmails()->first();
                $displayableEmail = $this->getApplicationUser()->getAccountUser()->getEmail();
            } elseif ($this->getApplicationUser()->getAccountUser() != null) {
                $displayableEmail = $this->getApplicationUser()->getAccountUser()->getEmail();
            }
        }
        return $displayableEmail;
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

}
