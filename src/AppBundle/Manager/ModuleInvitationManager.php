<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 21/07/2016
 * Time: 10:58
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ModuleInvitationManager
{
    /** Define the number of columns (ModuleInvitation) to display when displaying PollModule participations */
    const MAX_COLUMN_DISPLAYABLE = 20;

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


    /**
     * Retrieve a ModuleInvitaiton by its token. Can be NULL if it is not found
     * @param $token string Token of the moduleInvitation to look for
     * @return ModuleInvitation|null
     */
    public function retrieveModuleInvitationByToken($token)
    {
        $moduleInvitationRepo = $this->entityManager->getRepository(ModuleInvitation::class);
        $this->moduleInvitation = $moduleInvitationRepo->findOneBy(array('token' => $token));
        return $this->moduleInvitation;
    }

    /**
     * Initialize a ModuleInvitation for the current module and the given EventInvitation
     * NB : La ModuleInvitation n'est pas persist??e
     * @param $module Module le module pour lequel l'invitation est cr????e
     * @param $eventInvitation EventInvitation The EventInvitation owner of the ModuleInvitation
     * @param $createNew boolean If true, a new ModuleInvitation is created
     * @return ModuleInvitation
     */
    public function initializeModuleInvitation(Module $module, EventInvitation $eventInvitation, $createNew)
    {
        if ($this->moduleInvitation == null || $createNew) {
            $this->moduleInvitation = new ModuleInvitation();
        }
        if (empty($this->moduleInvitation->getToken())) {
            $this->moduleInvitation->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        }
        if ($this->moduleInvitation->getStatus() != ModuleInvitationStatus::EXCLUDED) {
            $this->moduleInvitation->setStatus(ModuleInvitationStatus::NOT_INVITED);
            // excluded reste excluded
        }

        $this->moduleInvitation->setCreator(false);
        $module->addModuleInvitation($this->moduleInvitation);
        $eventInvitation->addModuleInvitation($this->moduleInvitation);
        return $this->moduleInvitation;
    }

    /**
     * Initialise toutes les ModuleInvitations du module donn??, pour les invit??s de l'??v??nement
     * NB : Les ModuleInvitations ne sont pas persist??es
     * @param Event $event
     * @param Module $module
     */
    public function initializeModuleInvitationsForEvent(Event $event, Module $module)
    {
        /** @var EventInvitation $eventInvitation */
        foreach ($event->getEventInvitations() as $eventInvitation) {
            if ($eventInvitation->getStatus() != EventInvitationStatus::CANCELLED) {
                $this->moduleInvitation = $eventInvitation->getModuleInvitationForModule($module);
                if ($this->moduleInvitation == null) {
                    $this->initializeModuleInvitation($module, $eventInvitation, true);
                }
            }
        }
    }
}