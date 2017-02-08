<?php

namespace AppBundle\Entity\Event;

use AppBundle\Entity\Comment\CommentableInterface;
use AppBundle\Entity\Comment\Thread;
use AppBundle\Utils\enum\EventInvitationStatus;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Model\ThreadInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Event
 *
 * @ORM\Table(name="event_event")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Event\EventRepository")
 */
class Event implements CommentableInterface
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
     * This attributes stores the filename of the file for the database
     * @var string
     * @ORM\Column(name="picture_filename", type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @var float
     *
     * @ORM\Column(name="picture_focus_x", type="float", nullable=true)
     */
    private $pictureFocusX;

    /**
     * @var float
     *
     * @ORM\Column(name="picture_focus_y", type="float", nullable=true)
     */
    private $pictureFocusY;

    /**
     * @var int
     *
     * @ORM\Column(name="picture_width", type="integer", nullable=true)
     */
    private $pictureWidth;

    /**
     * @var int
     *
     * @ORM\Column(name="picture_height", type="integer", nullable=true)
     */
    private $pictureHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="where_name", type="string", length=255, nullable=true)
     */
    private $whereName;

    /**
     * @var string
     *
     * @ORM\Column(name="where_google_place_id", type="string", length=255, nullable=true)
     */
    private $whereGooglePlaceId;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="when_event", type="datetime", unique=false, nullable=true)
     */
    private $when;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=128, unique=true)
     */
    private $token;

    /**
     * @var string
     * @ORM\Column(name="status", type="enum_event_status")
     */
    private $status;

    /**
     * @var DateTime
     * @ORM\Column(name="response_deadline", type="datetime", unique=false, nullable=true)
     */
    private $responseDeadline;

    /**
     * If "true" then only guest with invitation can answer. No eventInivtaiton creation when displaying the event
     * @var boolean
     *
     * @ORM\Column(name="invitation_only", type="boolean")
     */
    private $invitationOnly = false;

    /**
     * * If "true" then guests can send invitations to others.
     * @var boolean
     * @ORM\Column(name="guests_can_invite", type="boolean")
     */
    private $guestsCanInvite = true;

    /**
     * * If "true" then guests can add module
     * @var boolean
     * @ORM\Column(name="guests_can_add_module", type="boolean")
     */
    private $guestsCanAddModule = true;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var Thread
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Comment\Thread")
     * @ORM\JoinColumn(name="comment_thread_id", referencedColumnName="id", nullable=true)
     */
    private $commentThread;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Event\Module", mappedBy="event", cascade={"persist"})
     * @ORM\OrderBy({"orderIndex" = "ASC"})
     */
    private $modules;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Event\EventInvitation", mappedBy="event", cascade={"persist"})
     */
    private $eventInvitations;

    /**
     * @var EventCoordinates
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Event\EventCoordinates", inversedBy="event", cascade={"persist"})
     * @ORM\JoinColumn(name="coordinates_id", referencedColumnName="id", nullable=true)
     */
    private $coordinates;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Event\EventOpeningHours", mappedBy="event", cascade={"persist"})
     */
    private $openingHours;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->modules = new ArrayCollection();
        $this->eventInvitations = new ArrayCollection();
        $this->openingHours = new ArrayCollection();
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * Set description
     *
     * @param string $description
     *
     * @return Event
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     * @return Event
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * @return float
     */
    public function getPictureFocusX()
    {
        return $this->pictureFocusX;
    }

    /**
     * @param float $pictureFocusX
     * @return Event
     */
    public function setPictureFocusX($pictureFocusX)
    {
        $this->pictureFocusX = $pictureFocusX;
        return $this;
    }

    /**
     * @return float
     */
    public function getPictureFocusY()
    {
        return $this->pictureFocusY;
    }

    /**
     * @param float $pictureFocusY
     * @return Event
     */
    public function setPictureFocusY($pictureFocusY)
    {
        $this->pictureFocusY = $pictureFocusY;
        return $this;
    }

    /**
     * @return int
     */
    public function getPictureWidth()
    {
        return $this->pictureWidth;
    }

    /**
     * @param int $pictureWidth
     * @return Event
     */
    public function setPictureWidth($pictureWidth)
    {
        $this->pictureWidth = $pictureWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getPictureHeight()
    {
        return $this->pictureHeight;
    }

    /**
     * @param int $pictureHeight
     * @return Event
     */
    public function setPictureHeight($pictureHeight)
    {
        $this->pictureHeight = $pictureHeight;
        return $this;
    }

    /**
     * @return string
     */
    public function getWhereName()
    {
        return $this->whereName;
    }

    /**
     * @param string $whereName
     */
    public function setWhereName($whereName)
    {
        $this->whereName = $whereName;
    }

    /**
     * @return string
     */
    public function getWhereGooglePlaceId()
    {
        return $this->whereGooglePlaceId;
    }

    /**
     * @param string $whereGooglePlaceId
     */
    public function setWhereGooglePlaceId($whereGooglePlaceId)
    {
        $this->whereGooglePlaceId = $whereGooglePlaceId;
    }

    /**
     * @return DateTime
     */
    public function getWhen()
    {
        return $this->when;
    }

    /**
     * @param DateTime $when
     */
    public function setWhen($when)
    {
        $this->when = $when;
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
     * Set token
     *
     * @param string $token
     *
     * @return Event
     */
    public function setToken($token)
    {
        $this->token = $token;
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
     * Set status
     *
     * @param string $status
     *
     * @return Event
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get responseDeadline
     *
     * @return DateTime
     */
    public function getResponseDeadline()
    {
        return $this->responseDeadline;
    }

    /**
     * Set responseDeadline
     *
     * @param DateTime $responseDeadline
     *
     * @return Event
     */
    public function setResponseDeadline($responseDeadline)
    {
        $this->responseDeadline = $responseDeadline;
        return $this;
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
     * @return Event
     */
    public function setInvitationOnly($invitationOnly)
    {
        $this->invitationOnly = $invitationOnly;
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
     * @return Event
     */
    public function setGuestsCanInvite($guestsCanInvite)
    {
        $this->guestsCanInvite = $guestsCanInvite;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isGuestsCanAddModule()
    {
        return $this->guestsCanAddModule;
    }

    /**
     * @param boolean $guestsCanAddModule
     * @return Event
     */
    public function setGuestsCanAddModule($guestsCanAddModule)
    {
        $this->guestsCanAddModule = $guestsCanAddModule;
        return $this;
    }

    /**
     * @return ThreadInterface
     */
    public function getCommentThread()
    {
        return $this->commentThread;
    }

    /**
     * @param ThreadInterface $commentThread
     * @return Event
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
        return $this->getToken();
    }

    /**
     * Get modules
     *
     * @return ArrayCollection
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Add module
     *
     * @param Module $module
     *
     * @return Event
     */
    public function addModule(Module $module)
    {
        $this->modules[] = $module;
        $module->setEvent($this);

        return $this;
    }

    /**
     * Remove module
     *
     * @param Module $module
     */
    public function removeModule(Module $module)
    {
        $this->modules->removeElement($module);
    }

    /**
     * Get eventInvitations
     *
     * @return ArrayCollection
     */
    public function getEventInvitations()
    {
        return $this->eventInvitations;
    }

    /**
     * Add eventInvitation
     *
     * @param EventInvitation $eventInvitation
     *
     * @return Event
     */
    public function addEventInvitation(EventInvitation $eventInvitation)
    {
        $this->eventInvitations[] = $eventInvitation;
        $eventInvitation->setEvent($this);

        return $this;
    }

    /**
     * Remove eventInvitation
     *
     * @param EventInvitation $eventInvitation
     */
    public function removeEventInvitation(EventInvitation $eventInvitation)
    {
        $this->eventInvitations->removeElement($eventInvitation);
    }

    /**
     * @return EventCoordinates
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * @param EventCoordinates $coordinates
     * @return Event
     */
    public function setCoordinates($coordinates)
    {
        $this->coordinates = $coordinates;
        return $this;
    }

    /**
     * Get openingHours
     *
     * @return ArrayCollection
     */
    public function getOpeningHours()
    {
        return $this->openingHours;
    }

    /**
     * Add eventInvitation
     *
     * @param EventInvitation $eventInvitation
     *
     * @return Event
     */
    public function addopeningHour(EventOpeningHours $openingHours)
    {
        $this->openingHours[] = $openingHours;
        $openingHours->setEvent($this);
        return $this;
    }

    /**
     * Remove openingHour
     *
     * @param EventOpeningHours $openingHours
     */
    public function removeOpeningHour(EventOpeningHours $openingHours)
    {
        $this->openingHours->removeElement($openingHours);
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
        return $this->eventInvitations->matching($criteria);
    }

    /**
     * Retrieve EventInvitation with administrator = true
     * @return Collection
     */
    public function getAdministrators()
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("administrator", true));
        return $this->eventInvitations->matching($criteria);
    }

    /**
     * Retrieve EventInvitations that are valid and matching the given $answer
     * @param (string|null) $answer
     * @return Collection
     */
    public function getEventInvitationByAnswer($answer = null)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->in("status", [EventInvitationStatus::AWAITING_ANSWER, "status", EventInvitationStatus::VALID]));
        if ($answer != null) {
            $criteria->andWhere(Criteria::expr()->eq("answer", $answer));
        }
        return $this->eventInvitations->matching($criteria);
    }

    /**
     * Retourne les EventInvitations pour lesquels l'email d'invitation n'a pas été envoyée
     * @return Collection
     */
    public function getEventInvitationEmailNotSent($excludeOrganizer = true)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->isNull("invitationEmailSentAt"))
            ->andWhere(Criteria::expr()->neq("status", EventInvitationStatus::CANCELLED))// TODO Verifier que ca marche quand la fonction sera utilisée
            ->andWhere(Criteria::expr()->neq("applicationUser", null));
        if ($excludeOrganizer) {
            $criteria
                ->andWhere(Criteria::expr()->eq('creator', false))
                ->andWhere(Criteria::expr()->eq('administrator', false));
        }
        return $this->eventInvitations->matching($criteria);
    }
}
