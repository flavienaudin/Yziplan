<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 22/03/2017
 * Time: 14:59
 */

namespace AppBundle\Utils\Notifications;


use AppBundle\Utils\enum\NotificationTypeEnum;

class Notification
{

    /** @var \DateTime Datetime of the notification event to order notifications */
    private $date;

    /** @var NotificationTypeEnum Object concerned by the notification */
    private $type;

    /** @var array Additional data usefull to generate notification text */
    private $datas;

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
     * @return NotificationTypeEnum
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param NotificationTypeEnum $type
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
    public function getDatas()
    {
        return $this->datas;
    }

    /**
     * @param array $datas
     * @return Notification
     */
    public function setDatas($datas)
    {
        $this->datas = $datas;
        return $this;
    }


    /***********************************************************************
     *                      UTILS
     ***********************************************************************/

    /**
     * Compare deux notifications par leur date.
     *
     * @param Notification $notificationA
     * @param Notification $notificationB
     * @return int 0 si les dates sont nulles ou égales. -1 si la notificationA est antérieure à la notificationB ou a une date null, -1 dans les autres cas
     */
    public static function compare(Notification $notificationA, Notification $notificationB)
    {
        if ($notificationA == $notificationB || $notificationA->getDate() == $notificationB->getDate()) {
            return 0;
        }
        if ($notificationA->getDate() == null || $notificationA->getDate() < $notificationB->getDate()) {
            return -1;
        } else {
            return 1;
        }
    }

}