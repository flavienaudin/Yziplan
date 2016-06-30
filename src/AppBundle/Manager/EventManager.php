<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/06/2016
 * Time: 11:39
 */

namespace AppBundle\Manager;

use AppBundle\Entity\enum\EventStatus;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvitation;
use AppBundle\Entity\Module;
use AppBundle\Form\EventFormType;
use AppBundle\Form\ModuleFormType;
use ATUserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class EventManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var FormFactory */
    private $formFactory;

    /** @var GenerateursToken */
    private $generateursToken;

    /** @var ModuleManager */
    private $moduleManager;

    /** @var Event L'événement en cours de traitement */
    private $event;

    public function __construct(EntityManager $doctrine, AuthorizationCheckerInterface $authorizationChecker, FormFactory $formFactory, GenerateursToken $generateurToken, ModuleManager $moduleManager)
    {
        $this->entityManager = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
        $this->moduleManager = $moduleManager;
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
     * @return EventManager
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @param $token string Le token de l'événement à récupérer
     * @param $tokenKey string La clé du token à utiliser : token ou tokenEdition
     * @return bool true Si un événement est trouvé
     */
    public function retrieveEvent($token, $tokenKey = 'token')
    {
        if (empty($token)) {
            $this->event = new Event();
        } else {
            if ($tokenKey == 'token' || $tokenKey == 'tokenEdition') {
                $eventRep = $this->entityManager->getRepository("AppBundle:Event");
                $this->event = $eventRep->findOneBy(array($tokenKey => $token));
            }
        }
        return ($this->event instanceof Event);
    }

    /**
     * @return Event l'événement initialisé en cours de création
     */
    public function initEvent()
    {
        if ($this->event->getStatus() == null) {
            $this->event->setStatus(EventStatus::IN_CREATION);
        }
        if (empty($this->event->getToken())) {
            $this->event->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        }
        if (empty($this->event->getTokenEdition())) {
            $this->event->setTokenEdition($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        }
        if (empty($this->getEvent()->getId())) {
            $this->entityManager->persist($this->getEvent());
            $this->entityManager->flush();
        }
        return $this->event;
    }

    /**
     * @param $user User|string Utilisateur connecté (ou anonyme)
     * @return Form Formulaire de création/édition d'un événement
     */
    public function initEventForm($user)
    {
        if ($this->event->getCreator() == null && $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED) && $user instanceof User) {
            $creatorInvitation = new EventInvitation();
            $creatorInvitation->setAppUser($user->getAppUser());
            $creatorInvitation->setEvent($this->event);
            $this->event->setCreator($creatorInvitation);
            $this->event->addEventInvitation($creatorInvitation);
        }
        return $this->formFactory->create(EventFormType::class, $this->event);
    }


    /**
     * Renseigne l'événement à partir des données soumises dans le formulaire lors d'une création ou édition
     * @param Form $evtForm
     * @return Event|mixed
     */
    public function treatEventFormSubmission(Form $evtForm)
    {
        $this->event = $evtForm->getData();

        if (empty($this->event->getToken())) {
            $this->event->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        }
        if (empty($this->event->getTokenEdition())) {
            $this->event->setTokenEdition($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        }

        $this->entityManager->persist($this->event);
        $this->entityManager->flush();

        return $this->event;
    }

    /**
     * Crée et ajoute un module à l'événement
     * @param $type string Le type du module à créer et à ajouter à l'événement
     * @return Module le module créé
     */
    public function addModule($type)
    {
        $module = $this->moduleManager->createModule($type);
        $this->event->addModule($module);
        $this->entityManager->persist($this->event);
        $this->entityManager->flush();
        return $module;
    }

    /**
     * @param User $user L'Utilisateur connecté ou non
     * @return array Un tableau de modules de l'événement au format :
     *  moduleId => [
     *  'module' => Module : Le module lui-meme
     *  'allowEdit => boolean : si l'utilisateur est autorisé à l'éditer
     *  'moduleForm' => Form : le formulaire d'édition de l'événement si editable
     * ]
     */
    public function getModulesToDisplay(User $user = null)
    {
        $modules = array();
        if ($this->event != null) {
            /** @var Module $module */
            foreach ($this->event->getModules() as $module){
                $moduleDescription['module'] = $module;
                $moduleDescription['allowEdit'] = true;
                $moduleDescription['moduleForm'] = $this->formFactory->createNamed("module_form_".$module->getTokenEdition(), ModuleFormType::class, $module);
                $modules[$module->getId()]= $moduleDescription;
            }
        }
        return $modules;
    }

}