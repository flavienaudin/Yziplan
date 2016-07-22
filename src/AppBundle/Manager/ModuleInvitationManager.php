<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 21/07/2016
 * Time: 10:58
 */

namespace AppBundle\Manager;


use AppBundle\Entity\enum\ModuleInvitationStatus;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvitation;
use AppBundle\Entity\Module;
use AppBundle\Entity\ModuleInvitation;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ModuleInvitationManager
{

    /** @var EntityManager */
    private $entityManager;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var GenerateursToken */
    private $generateursToken;

    /** @var ModuleInvitation L'invitation au module en cours de traitement */
    private $moduleInvitation;


    public function __construct(EntityManager $doctrine, AuthorizationCheckerInterface $authorizationChecker, GenerateursToken $generateurToken)
    {
        $this->entityManager = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
        $this->generateursToken = $generateurToken;
    }

    /**
     * @return ModuleInvitation
     */
    public function getModuleInvitation()
    {
        return $this->moduleInvitation;
    }

    /**
     * @param ModuleInvitation $moduleInvitation
     * @return ModuleInvitationManager
     */
    public function setModuleInvitation($moduleInvitation)
    {
        $this->moduleInvitation = $moduleInvitation;
        return $this;
    }


    public function retrieveModuleInvitation(EventInvitation $eventInvitation, Module $module){
        $this->moduleInvitation = $eventInvitation->getModuleInvitationForModule($module);
        if($this->moduleInvitation == null){
            $this->moduleInvitation = $this->initializeModuleInvitation($module, $eventInvitation);
        }
        return $this->moduleInvitation;
    }

    /**
     * Initialize a ModuleInvitation for the current module and the given EventInvitation
     * @param $eventInvitation EventInvitation The EventInvitation owner of the ModuleInvitation
     * @return ModuleInvitation
     */
    public function initializeModuleInvitation(Module $module, EventInvitation $eventInvitation)
    {
        if($this->moduleInvitation == null) {
            $this->moduleInvitation = new ModuleInvitation();
        }
        $this->moduleInvitation->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        $this->moduleInvitation->setTokenEdition($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        $this->moduleInvitation->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $module->addModuleInvitation($this->moduleInvitation);
        $eventInvitation->addModuleInvitation($this->moduleInvitation);
        return $this->moduleInvitation;
    }

    public function initializeModuleInvitationsForEvent(Event $event, Module $module){
        /** @var EventInvitation $eventInvitation */
        foreach($event->getEventInvitations() as $eventInvitation){
            $this->moduleInvitation = $eventInvitation->getModuleInvitationForModule($module);
            if($this->moduleInvitation == null){
                $this->initializeModuleInvitation($module, $eventInvitation);
            }
        }
    }

}