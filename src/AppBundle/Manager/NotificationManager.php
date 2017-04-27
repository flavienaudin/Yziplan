<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 06/04/2017
 * Time: 17:07
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Comment\Comment;
use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Notifications\Notification;
use AppBundle\Mailer\AppMailer;
use AppBundle\Utils\enum\EventInvitationAnswer;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\NotificationFrequencyEnum;
use AppBundle\Utils\enum\NotificationTypeEnum;
use Doctrine\ORM\EntityManager;

class NotificationManager
{
    /** @var EntityManager */
    private $entityManager;
    /** @var AppMailer */
    private $appMailer;

    /** @var Notification $notification */
    private $notification;

    public function __construct(EntityManager $doctrine, AppMailer $appMailer)
    {
        $this->entityManager = $doctrine;
        $this->appMailer = $appMailer;
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param Notification $notification
     * @return NotificationManager
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @param Module $module
     * @param EventInvitation $creatorEventInvitation
     */
    public function createAddModuleNotifications(Module $module, EventInvitation $creatorEventInvitation)
    {
        $new_notification_date = $module->getCreatedAt();
        $new_notification_type = NotificationTypeEnum::ADD_MODULE;
        $creatorNames = "";
        /** @var ModuleInvitation $creator */
        foreach ($module->getCreators() as $creator) {
            $creatorNames .= (!empty($creatorNames) ? ' ,' : '') . $creator->getDisplayableName(true, true);
        }
        $data = array(
            "subject" => array(
                'token' => $module->getToken(),
                'name' => $module->getName()
            ),
            "creator_names" => $creatorNames
        );

        // Gestion des notifications
        /** @var ModuleInvitation $moduleInvitation */
        foreach ($module->getModuleInvitations() as $moduleInvitation) {
            $eventInvitation = $moduleInvitation->getEventInvitation();
            if ($eventInvitation !== $creatorEventInvitation && $eventInvitation->getStatus() != EventInvitationStatus::CANCELLED) {
                $new_module_notification = new Notification();
                $new_module_notification->setDate($new_notification_date);
                $new_module_notification->setType($new_notification_type);
                $new_module_notification->setData($data);
                $eventInvitation->addNotification($new_module_notification);
                $this->entityManager->persist($new_module_notification);

                if ($eventInvitation->getEventInvitationPreferences()->getNotifEmailFrequency() !== NotificationFrequencyEnum::NEVER
                    && $eventInvitation->getEventInvitationPreferences()->isNotifNewModule()
                ) {
                    $this->appMailer->sendNewNotificationEmail($eventInvitation, $new_module_notification, $creatorEventInvitation);
                }
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @param PollProposal $pollProposal
     * @param EventInvitation|null $creatorEventInvitation
     */
    public function createAddPollProposalNotifications(PollProposal $pollProposal, EventInvitation $creatorEventInvitation = null)
    {
        $module = $pollProposal->getPollModule()->getModule();
        $new_notification_date = $module->getCreatedAt();
        $new_notification_type = NotificationTypeEnum::ADD_POLL_PROPOSAL;
        $creatorName = $pollProposal->getCreator()->getDisplayableName(true, true);
        $data = array(
            "subject" => array(
                'token' => $module->getToken(),
                'name' => $module->getName()
            ),
            "creator_name" => $creatorName
        );

        // Gestion des notifications
        /** @var ModuleInvitation $moduleInvitation */
        foreach ($module->getModuleInvitations() as $moduleInvitation) {
            $eventInvitation = $moduleInvitation->getEventInvitation();
            if ($eventInvitation !== $creatorEventInvitation && $eventInvitation->getStatus() != EventInvitationStatus::CANCELLED) {
                $new_pollproposal_notification = new Notification();
                $new_pollproposal_notification->setDate($new_notification_date);
                $new_pollproposal_notification->setType($new_notification_type);
                $new_pollproposal_notification->setData($data);
                $eventInvitation->addNotification($new_pollproposal_notification);
                $this->entityManager->persist($new_pollproposal_notification);


                if ($eventInvitation->getEventInvitationPreferences()->getNotifEmailFrequency() !== NotificationFrequencyEnum::NEVER
                    && $eventInvitation->getEventInvitationPreferences()->isNotifNewPollpropsal()
                ) {
                    $this->appMailer->sendNewNotificationEmail($eventInvitation, $new_pollproposal_notification, $creatorEventInvitation);
                }
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @param Comment $comment
     * @param Event|Module $subject
     * @param EventInvitation $creatorEventInvitation
     */
    public function createNewCommentNotifications(Comment $comment, $subject, EventInvitation $creatorEventInvitation)
    {
        $new_notification_date = $comment->getCreatedAt();
        $new_notification_type = NotificationTypeEnum::POST_COMMENT;
        $data = array(
            "new_comments_number" => 1,
            "message" => $comment->getBody(),
            "subject" => array(
                'type' => ($subject instanceof Module ? 'module' : 'event'),
                'token' => $subject->getToken(),
                'name' => $subject->getName()
            )
        );

        // Gestion des notifications
        /** @var Event $event */
        $event = ($subject instanceof Module ? $subject->getEvent() : $subject);
        /** @var EventInvitation $eventInvitation */
        foreach ($event->getEventInvitationByAnswer([EventInvitationAnswer::YES, EventInvitationAnswer::DONT_KNOW, EventInvitationAnswer::NO]) as $eventInvitation) {
            if ($eventInvitation !== $creatorEventInvitation) {
                $new_comment_notification = new Notification();
                $new_comment_notification->setDate($new_notification_date);
                $new_comment_notification->setType($new_notification_type);
                $new_comment_notification->setData($data);
                $eventInvitation->addNotification($new_comment_notification);
                $this->entityManager->persist($new_comment_notification);


                if ($eventInvitation->getEventInvitationPreferences()->getNotifEmailFrequency() !== NotificationFrequencyEnum::NEVER
                    && $eventInvitation->getEventInvitationPreferences()->isNotifNewComment()
                ) {
                    $this->appMailer->sendNewNotificationEmail($eventInvitation, $new_comment_notification, $creatorEventInvitation);
                }
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Delete all notifications of EventInvitation
     * @param EventInvitation $eventInvitation
     */
    public function markAllView(EventInvitation $eventInvitation)
    {
        /** @var Notification $notification */
        foreach ($eventInvitation->getNotifications() as $notification) {
            $this->entityManager->remove($notification);
        }
        $eventInvitation->removeAllNotifications();
        $this->entityManager->flush();
    }

    /**
     * Delete a notification of EventInvitation
     * @param Notification $notification
     */
    public function markAsView(Notification $notification)
    {
        $notification->getEventInvitation()->removeNotification($notification);
        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }
}