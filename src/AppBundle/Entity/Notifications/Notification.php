<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 22/03/2017
 * Time: 14:59
 */

namespace AppBundle\Entity\Notifications;

use AppBundle\Entity\Event\EventInvitation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Notification
 *
 * @ORM\Table(name="event_event_invitation_notification")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Event\EventInvitationNotificationRepository")
 */
class Notification
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
     * @var \DateTime Datetime of the notification event to order notifications
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var string Class of object concerned by the notification
     * @ORM\Column(name="type", type="enum_notification_type", length=128, nullable=false)
     */
    private $type;

    /**
     * @var array Additional data usefull to generate notification text
     * @ORM\Column(name="data", type="array", nullable=true)
     */
    private $data;


    /**************************************************************************************************************
     *                                      Jointures
     **************************************************************************************************************/

    /**
     * @var EventInvitation L'invitation qui doit afficher la notification
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\EventInvitation", inversedBy="notifications")
     * @ORM\JoinColumn(name="event_invitation_id", referencedColumnName="id")
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
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return Notification
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Notification
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return Notification
     */
    public function setData($data)
    {
        $this->data = $data;
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
     * @return Notification
     */
    public function setEventInvitation($eventInvitation)
    {
        $this->eventInvitation = $eventInvitation;
        return $this;
    }

    /***********************************************************************
     *                      UTILS
     ***********************************************************************/

    /**
     * Compare deux notifications par leur date : DESC
     *
     * @param Notification $notificationA
     * @param Notification $notificationB
     * @return int 0 si les dates sont nulles ou égales. 1 si la notificationA est antérieure à la notificationB ou a une date null, -1 dans les autres cas
     */
    public static function compareDesc(Notification $notificationA, Notification $notificationB)
    {
        if ($notificationA === $notificationB || $notificationA->getDate() === $notificationB->getDate()) {
            return 0;
        }
        if ($notificationA->getDate() == null || $notificationA->getDate() < $notificationB->getDate()) {
            return 1;
        } else {
            return -1;
        }
    }

    /**
     * Compare deux notifications par leur date : ASC
     *
     * @param Notification $notificationA
     * @param Notification $notificationB
     * @return int 0 si les dates sont nulles ou égales. -1 si la notificationA est antérieure à la notificationB ou a une date null, 1 dans les autres cas
     */
    public static function compareAsc(Notification $notificationA, Notification $notificationB)
    {
        if ($notificationA === $notificationB || $notificationA->getDate() === $notificationB->getDate()) {
            return 0;
        }
        if ($notificationA->getDate() == null || $notificationA->getDate() < $notificationB->getDate()) {
            return -1;
        } else {
            return 1;
        }
    }

}