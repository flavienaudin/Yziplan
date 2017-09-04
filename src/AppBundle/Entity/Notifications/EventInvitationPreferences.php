<?php

namespace AppBundle\Entity\Notifications;

use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Utils\enum\NotificationFrequencyEnum;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * EventInvitationPreferences
 *
 * @ORM\Table(name="event_invitation_preferences")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Notifications\EventInvitationPreferencesRepository")
 */
class EventInvitationPreferences
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
     * @var bool
     *
     * @ORM\Column(name="notif_new_comment", type="boolean")
     */
    private $notifNewComment = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="notif_new_comment_last_email_at", type="datetime", nullable=true)
     */
    private $notifNewCommentLastEmailAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="notif_new_module", type="boolean")
     */
    private $notifNewModule = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="notif_new_module_last_email_at", type="datetime", nullable=true)
     */
    private $notifNewModuleLastEmailAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="notif_new_pollpropsal", type="boolean")
     */
    private $notifNewPollpropsal = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="notif_new_pollproposal_last_email_at", type="datetime", nullable=true)
     */
    private $notifNewPollproposalLastEmailAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="notif_change_poll_module_voting_type", type="boolean")
     */
    private $notifChangePollModuleVotingType = true;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="notif_change_poll_module_voting_type_last_email_at", type="datetime", nullable=true)
     */
    private $notifChangePollModuleVotingTypeLastEmailAt;

    /**
     * @var string
     *
     * @ORM\Column(name="notif_email_frequency", type="enum_notification_frequency", length=63)
     */
    private $notifEmailFrequency = NotificationFrequencyEnum::EACH_NOTIFICATION;


    /**************************************************************************************************************
     *                                      Jointures
     **************************************************************************************************************/

    /**
     * @var EventInvitation L'invitation attaché à ces préférences
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Event\EventInvitation", mappedBy="eventInvitationPreferences")
     */
    private $eventInvitation;


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
     * @return bool
     */
    public function isNotifNewComment()
    {
        return $this->notifNewComment;
    }

    /**
     * @param bool $notifNewComment
     * @return EventInvitationPreferences
     */
    public function setNotifNewComment($notifNewComment)
    {
        $this->notifNewComment = $notifNewComment;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getNotifNewCommentLastEmailAt()
    {
        return $this->notifNewCommentLastEmailAt;
    }

    /**
     * @param \DateTime $notifNewCommentLastEmailAt
     * @return EventInvitationPreferences
     */
    public function setNotifNewCommentLastEmailAt($notifNewCommentLastEmailAt)
    {
        $this->notifNewCommentLastEmailAt = $notifNewCommentLastEmailAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNotifNewModule()
    {
        return $this->notifNewModule;
    }

    /**
     * @param bool $notifNewModule
     * @return EventInvitationPreferences
     */
    public function setNotifNewModule($notifNewModule)
    {
        $this->notifNewModule = $notifNewModule;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getNotifNewModuleLastEmailAt()
    {
        return $this->notifNewModuleLastEmailAt;
    }

    /**
     * @param \DateTime $notifNewModuleLastEmailAt
     * @return EventInvitationPreferences
     */
    public function setNotifNewModuleLastEmailAt($notifNewModuleLastEmailAt)
    {
        $this->notifNewModuleLastEmailAt = $notifNewModuleLastEmailAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNotifNewPollpropsal()
    {
        return $this->notifNewPollpropsal;
    }

    /**
     * @param bool $notifNewPollpropsal
     * @return EventInvitationPreferences
     */
    public function setNotifNewPollpropsal($notifNewPollpropsal)
    {
        $this->notifNewPollpropsal = $notifNewPollpropsal;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getNotifNewPollproposalLastEmailAt()
    {
        return $this->notifNewPollproposalLastEmailAt;
    }

    /**
     * @param \DateTime $notifNewPollproposalLastEmailAt
     * @return EventInvitationPreferences
     */
    public function setNotifNewPollproposalLastEmailAt($notifNewPollproposalLastEmailAt)
    {
        $this->notifNewPollproposalLastEmailAt = $notifNewPollproposalLastEmailAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNotifChangePollModuleVotingType()
    {
        return $this->notifChangePollModuleVotingType;
    }

    /**
     * @param bool $notifChangePollModuleVotingType
     * @return EventInvitationPreferences
     */
    public function setNotifChangePollModuleVotingType($notifChangePollModuleVotingType)
    {
        $this->notifChangePollModuleVotingType = $notifChangePollModuleVotingType;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getNotifChangePollModuleVotingTypeLastEmailAt()
    {
        return $this->notifChangePollModuleVotingTypeLastEmailAt;
    }

    /**
     * @param \DateTime $notifChangePollModuleVotingTypeLastEmailAt
     * @return EventInvitationPreferences
     */
    public function setNotifChangePollModuleVotingTypeLastEmailAt($notifChangePollModuleVotingTypeLastEmailAt)
    {
        $this->notifChangePollModuleVotingTypeLastEmailAt = $notifChangePollModuleVotingTypeLastEmailAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotifEmailFrequency()
    {
        return $this->notifEmailFrequency;
    }

    /**
     * @param string $notifEmailFrequency
     * @return EventInvitationPreferences
     */
    public function setNotifEmailFrequency($notifEmailFrequency)
    {
        $this->notifEmailFrequency = $notifEmailFrequency;
        return $this;
    }

    /**
     * @return EventInvitation
     */
    public function getEventInvitation()
    {
        return $this->eventInvitation;
    }

    /**
     * @param EventInvitation $eventInvitation
     * @return EventInvitationPreferences
     */
    public function setEventInvitation($eventInvitation)
    {
        $this->eventInvitation = $eventInvitation;
        return $this;
    }
}

