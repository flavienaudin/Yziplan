<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/06/2016
 * Time: 11:39
 */

namespace AppBundle\Manager;

use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Event\EventOpeningHours;
use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Form\Event\EventTemplateSettingsType;
use AppBundle\Form\Event\EventType;
use AppBundle\Form\Event\InvitationsType;
use AppBundle\Form\Event\SendMessageType;
use AppBundle\Mailer\AppMailer;
use AppBundle\Security\ModuleVoter;
use AppBundle\Security\PollProposalVoter;
use AppBundle\Utils\enum\EventInvitationAnswer;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\EventStatus;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\enum\ModuleStatus;
use AppBundle\Utils\Response\AppJsonResponse;
use ATUserBundle\Entity\AccountUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class EventManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var  TokenStorageInterface */
    private $tokenStorage;

    /** @var Session */
    private $session;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var RouterInterface */
    private $router;

    /** @var EngineInterface */
    private $templating;

    /** @var TranslatorInterface $translator */
    private $translator;

    /** @var FormFactory */
    private $formFactory;

    /** @var GenerateursToken */
    private $generateursToken;

    /** @var ModuleManager */
    private $moduleManager;

    /** @var PollProposalManager */
    private $pollProposalManager;

    /** @var  EventInvitationManager */
    private $eventInvitationManager;

    /** @var ModuleInvitationManager */
    private $moduleInvitationManager;

    /** @var DiscussionManager */
    private $discussionManager;

    /** @var AppMailer */
    private $appMailer;

    /** @var NotificationManager */
    private $notificationManager;

    /** @var Event L'??v??nement en cours de traitement */
    private $event;

    /** @var ArrayCollection Les OpeninghOurs de l'??vn??ment pour mise ?? jour */
    private $eventOriginalOpeningHours;

    public function __construct(EntityManager $doctrine, TokenStorageInterface $tokenStorage, Session $session, AuthorizationCheckerInterface $authorizationChecker, RouterInterface $router,
                                EngineInterface $templating, TranslatorInterface $translator, FormFactory $formFactory, GenerateursToken $generateurToken, ModuleManager $moduleManager,
                                PollProposalManager $pollProposalManager, EventInvitationManager $eventInvitationManager, ModuleInvitationManager $moduleInvitationManager,
                                DiscussionManager $discussionManager, AppMailer $appMailer, NotificationManager $notificationManager)
    {
        $this->entityManager = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
        $this->moduleManager = $moduleManager;
        $this->pollProposalManager = $pollProposalManager;
        $this->eventInvitationManager = $eventInvitationManager;
        $this->moduleInvitationManager = $moduleInvitationManager;
        $this->discussionManager = $discussionManager;
        $this->appMailer = $appMailer;
        $this->notificationManager = $notificationManager;
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
     * @param $token string Le token de l'??v??nement ?? r??cup??rer
     * @return Event|null L'??v??nement trouv?? sinon null
     */
    public function retrieveEvent($token)
    {
        if (empty($token)) {
            $this->initializeEvent();
        } else {
            $eventRep = $this->entityManager->getRepository(Event::class);
            $this->event = $eventRep->findOneBy(array('token' => $token));
        }
        return $this->event;
    }

    /**
     * @return Event l'??v??nement initialis?? en cours de cr??ation
     */
    public function initializeEvent()
    {
        $this->event = new Event();
        if ($this->event->getStatus() == null) {
            $this->event->setStatus(EventStatus::IN_CREATION);
        }
        if (empty($this->event->getToken())) {
            $this->event->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        }
        $user = $this->tokenStorage->getToken()->getUser();
        $this->eventInvitationManager->createCreatorEventInvitation($this->event, ($user instanceof AccountUser ? $user->getApplicationUser() : null));

        if (empty($this->event->getId())) {
            $this->entityManager->persist($this->event);
            $this->entityManager->flush();
        }
        return $this->event;
    }

    /**
     * @return Event The Event updated
     */
    public function persistEvent()
    {
        $this->entityManager->persist($this->event);
        $this->entityManager->flush();
        return $this->event;
    }

    /**
     * @return FormInterface Formulaire de cr??ation/??dition d'un ??v??nement
     */
    public function initEventForm()
    {
        // Create an ArrayCollection of the current OpeningHour objects in the database
        $this->eventOriginalOpeningHours = new ArrayCollection();
        foreach ($this->event->getOpeningHours() as $openingHour) {
            $this->eventOriginalOpeningHours->add($openingHour);
        }
        return $this->formFactory->create(EventType::class, $this->event);
    }

    /**
     * Renseigne l'??v??nement ?? partir des donn??es soumises dans le formulaire lors d'une cr??ation ou ??dition
     * @param FormInterface $evtForm
     * @return Event|mixed
     */
    public function treatEventFormSubmission(FormInterface $evtForm, EventInvitation $userEventInvitation)
    {
        $this->event = $evtForm->getData();
        if (empty($this->event->getToken())) {
            $this->event->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        }
        if ($this->event->isTemplate() && empty($this->event->getTokenDuplication())) {
            $this->event->setTokenDuplication($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
            $this->event->setDuplicationEnabled(true);
        }
        // L'??v??nement est en cours d'organisation si le cr??ateur a une invitation valide
        if ($this->event->getStatus() == EventStatus::IN_CREATION && $userEventInvitation->isCreator() &&
            ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER || $userEventInvitation->getStatus() == EventInvitationStatus::VALID)
        ) {
            $this->event->setStatus(EventStatus::IN_ORGANIZATION);
        }
        if (empty($this->event->getWhereName())) {
            $this->event->setWhereGooglePlaceId(null);
        }

        // remove non-valid $openingHours
        /** @var EventOpeningHours $postedOpeningHours */
        foreach ($this->event->getOpeningHours() as $postedOpeningHours) {
            if (!$postedOpeningHours->isValid()) {
                $this->event->removeOpeningHour($postedOpeningHours);
            }
        }

        // remove the relationship between the OpeningHours and the Event
        /** @var EventOpeningHours $openingHour */
        foreach ($this->eventOriginalOpeningHours as $openingHour) {
            if (false === $this->event->getOpeningHours()->contains($openingHour)) {
                // remove the OpeningHours from the Event
                $this->event->removeOpeningHour($openingHour);
                $this->entityManager->remove($openingHour);
            }
        }
        $this->entityManager->persist($this->event);
        $this->entityManager->flush();

        return $this->event;
    }

    /**
     * Duplique l'??v??nement pass?? en param??tre (ou celui en cours). Les modules sont dupliqu??s ou non selon le param??tre $duplicateModules
     * L'??v??nement n'est pas persist?? en base de donn??es
     *
     * @param boolean $duplicateModules Les modules sont ??galement dupliqu??s et associ??s au nouvel ??v??nement
     * @param Event|null $event L'??v??nement ?? dupliquer (si non donn??,  l'attribut "event" de la classe est utilis??)
     * @return Event R??sultat de la duplication de l'??v??nement
     */
    public function duplicateEvent($duplicateModules, Event $event = null)
    {
        if ($event != null) {
            $this->event = $event;
        }
        if ($this->event != null && $this->event->isDuplicationEnabled()) {
            $duplicatedEvent = new Event();
            $duplicatedEvent->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
            $duplicatedEvent->setStatus(EventStatus::IN_CREATION);
            $duplicatedEvent->setName($this->event->getName());
            $duplicatedEvent->setDescription($this->event->getDescription());
            $duplicatedEvent->setResponseDeadline($this->event->getResponseDeadline());
            $duplicatedEvent->setEventParent($this->event);
            $duplicatedEvent->setTemplate(false);
            $duplicatedEvent->setDuplicationEnabled(false);

            if ($this->event->getPictureFile() != null) {
                $originalFile = $this->event->getPictureFile();
                $tempFileCopyName = str_replace($originalFile->getExtension(), 'bak.' . $originalFile->getExtension(), $originalFile->getFilename());
                $tempFileCopyPathname = $this->event->getPictureFile()->getPath() . '/' . $tempFileCopyName;
                if (copy($this->event->getPictureFile()->getPathname(), $tempFileCopyPathname)) {
                    $newFile = new UploadedFile($tempFileCopyPathname, $tempFileCopyName, $originalFile->getMimeType(), $originalFile->getSize(), null, true);
                    $duplicatedEvent->setPictureFile($newFile);

                    $duplicatedEvent->setPictureFocusX($this->event->getPictureFocusX());
                    $duplicatedEvent->setPictureFocusY($this->event->getPictureFocusY());
                    $duplicatedEvent->setPictureWidth($this->event->getPictureWidth());
                    $duplicatedEvent->setPictureHeight($this->event->getPictureHeight());
                }
            }

            $duplicatedEvent->setWhereName($this->event->getWhereName());
            $duplicatedEvent->setWhereGooglePlaceId($this->event->getWhereGooglePlaceId());
            $duplicatedEvent->setWhen($this->event->getWhen());

            if ($this->event->getCoordinates() != null) {
                $this->event->getCoordinates()->duplicate($duplicatedEvent);
            }
            foreach ($this->event->getOpeningHours() as $openingHour) {
                $openingHour->duplicate($duplicatedEvent);
            }

            $duplicatedEvent->setInvitationOnly($this->event->isInvitationOnly());
            $duplicatedEvent->setGuestsCanInvite($this->event->isGuestsCanInvite());
            $duplicatedEvent->setGuestsCanAddModule($this->event->isGuestsCanAddModule());

            if ($duplicateModules) {
                /** @var Module $originalModule */
                foreach ($event->getModules() as $originalModule) {
                    if ($originalModule->getStatus() == ModuleStatus::IN_ORGANIZATION) {
                        /** @var Module $duplicatedModule */
                        $duplicatedModule = $this->moduleManager->duplicateModule($originalModule);
                        $duplicatedEvent->addModule($duplicatedModule);
                    }
                }
            }
            return $duplicatedEvent;
        } else {
            return null;
        }
    }

    public function createEventInvitationsForm()
    {
        return $this->formFactory->create(InvitationsType::class);
    }

    /**
     * @param FormInterface $eventInvitationsForm Formulaire d'envoi d'invitations : contenant les adreses emails et un message optionnel
     * @param array $resultCreation Tableau de r??sultat de cr??ation/envoi des invitations
     * @return Event|null
     */
    public function treatEventInvitationsFormSubmission(FormInterface $eventInvitationsForm, &$resultCreation = array())
    {
        if ($this->event != null) {
            $emailsData = $eventInvitationsForm->get("invitations")->getData();
            if ($eventInvitationsForm->has('message')) {
                $message = $eventInvitationsForm->get('message')->getData();
            } else {
                $message = null;
            }
            $resultCreation = $this->eventInvitationManager->createInvitations($this->event, $emailsData, $message);

            $this->entityManager->persist($this->event);
            $this->entityManager->flush();
            return $this->event;
        }
        return null;
    }

    /**
     * G??n??re le formulaire pour envoyer un rappel aux invit??s
     *
     * @return FormInterface
     */
    public function createSendMessageForm()
    {
        return $this->formFactory->create(SendMessageType::class);
    }

    /**
     * Traite la soumission du formulaire d'envoie d'un message aux invit??s
     * @param Form $sendMessageForm
     * @param EventInvitation $userEventInvitation L'invitation de l'utilisateur connect?? qui envoye le message
     * @return false|array : false if error, array of failed recipients
     */
    public function treatSendMessageFormSubmission(FormInterface $sendMessageForm, EventInvitation $userEventInvitation)
    {
        if ($this->event != null) {
            $message = $sendMessageForm->get("message")->getData();
            $selection = $sendMessageForm->get("selection")->getData();
            if ($selection == null || !is_array($selection)) {
                $selection = array();
            }
            $getAnswerNull = in_array(EventInvitationAnswer::DONT_KNOW, $selection);
            $recipients = $this->event->getEventInvitationByAnswer($selection, $getAnswerNull);
            if ($recipients->contains($userEventInvitation)) {
                // On n'envoit pas le message ?? l'exp??diteur
                $recipients->removeElement($userEventInvitation);
            }
            $failedRecipients = array();
            $this->eventInvitationManager->sendMessage($recipients, $message, $failedRecipients);
            return $failedRecipients;
        } else {
            return false;
        }
    }

    /**
     * G??n??re le formulaire de configuration de la duplication de type Template
     * @return FormInterface
     */
    public function createTemplateSettingsForm()
    {
        return $this->formFactory->create(EventTemplateSettingsType::class);
    }

    /**
     * Traite la soumission du formulaire de configuration de la duplication de type Template
     * @param FormInterface $templateSettingsForm
     * @return Event
     */
    public function treatTemplateSettingsForm(FormInterface $templateSettingsForm)
    {
        if ($templateSettingsForm->get("activateTemplate")->getData()) {
            if (empty($this->event->getTokenDuplication())) {
                $this->event->setTokenDuplication($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
            }
            $this->event->setDuplicationEnabled(true);
            $this->persistEvent();
        } else {
            $this->event->setDuplicationEnabled(false);
            $this->persistEvent();
        }
        return $this->event;
    }

    /**
     * Set the event.guestsCanInvite parameter
     * @param $requestData array with :
     *          - parameter string {invitationOnly;guestsCanInvite} the parameter to set
     *          - value boolean the new value to set
     * @param Event|null $event
     * @return bool
     */
    public function setEventParameter($requestData, Event $event = null)
    {
        if ($event != null) {
            $this->event = $event;
        }
        if ($this->event != null && isset($requestData['parameter']) && isset($requestData['value'])) {
            $parameter = $requestData['parameter'];
            $value = $requestData['value'];
            if ($parameter === "invitationOnly") {
                $this->event->setInvitationOnly($value);
            } elseif ($parameter === "guestsCanInvite") {
                $this->event->setGuestsCanInvite($value);
            } else {
                return false;
            }
            $this->persistEvent();
            return true;
        }
        return false;
    }

    /**
     * Annule un ??v??nement en modifiant le status et en envoyant un email ?? tous les invit??s
     * @param $requestData array Les donn??es de la requ??te contenant le message
     * @param $userEventInvitation EventInvitation l'invitation de l'utilisateur en cours
     * @param $event Event|null L'??v??nement ?? traiter
     * @return bool
     */
    public function cancelEvent($requestData, EventInvitation $userEventInvitation, Event $event = null)
    {
        if ($event != null) {
            $this->event = $event;
        }
        if ($this->event != null) {
            $this->event->setStatus(EventStatus::DEPROGRAMMED);
            $message = null;
            if (key_exists("reason", $requestData) && !empty($requestData['reason'])) {
                $this->event->setCancellationReason($requestData['reason']);
                $message = $this->event->getCancellationReason();
            }

            $guests = $this->event->getGuests();
            foreach ($guests as $guest) {
                $this->appMailer->sendCancellationEmail($guest, $message);
            }
            $organizers = $this->event->getOrganizers();
            foreach ($organizers as $organizer) {
                if ($organizer != $userEventInvitation) {
                    $this->appMailer->sendCancellationEmail($organizer, $message);
                }
            }

            $this->persistEvent();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Cr??e et ajoute un module ?? l'??v??nement
     * @param $type string Le type du module ?? cr??er et ?? ajouter ?? l'??v??nement (Cf. ModuleType)
     * @param $subtype string Le sous-type du module en fonction du type (Cf. PollModuleType et autre)
     * @param $creatorEventInvitation EventInvitation L'invitation ?? l'??v??nement du cr??ateur du module
     * @return Module le module cr????
     */
    public function addModule($type, $subtype, EventInvitation $creatorEventInvitation)
    {
        $module = $this->moduleManager->createModule($this->event, $type, $subtype, $creatorEventInvitation);
        // Initialisation des invitations au module avec status NOT_INVITED en attendant la publication du module
        $this->moduleInvitationManager->initializeModuleInvitationsForEvent($this->event, $module);

        //$this->entityManager->persist($creatorEventInvitation);
        $this->entityManager->persist($this->event);
        $this->entityManager->flush();

        // Create the thread after the module affectation to its event because of the thread ID is generate with the event token
        $this->discussionManager->createCommentableThread($module);

        return $module;
    }

    /**
     * @param EventInvitation $userEventInvitation
     * @param string $requestUri The URI of the request to create thread for modules
     * @return array Un tableau de modules de l'??v??nement au format :
     *  moduleId => [
     *  'module' => Module : Le module lui-meme
     *  'userModuleInvitation' => ModuleInvitation : L'invitation au module de l'utilisateur courant
     *  'moduleForm' => Form : le formulaire d'??dition de l'??v??nement si editable
     *  'pollProposalAddForm' => Form : uniquement pour un PollModule
     * ]
     */
    public function getModulesToDisplay(EventInvitation $userEventInvitation)
    {
        $modules = array();
        if ($this->event != null) {
            $eventModules = $this->entityManager->getRepository(Module::class)->findOrderedByEventToken($this->event->getToken());
            /** @var Module $module */
            foreach ($eventModules as $module) {
                $userModuleInvitation = $userEventInvitation->getModuleInvitationForModule($module);
                if ($this->authorizationChecker->isGranted(ModuleVoter::DISPLAY, array($module, $userModuleInvitation))) {
                    $moduleDescription = array();
                    $moduleDescription['module'] = $module;
                    $moduleDescription['userModuleInvitation'] = $userModuleInvitation;
                    $thread = $module->getCommentThread();
                    if (null == $thread) {
                        $thread = $this->discussionManager->createCommentableThread($module);
                    }
                    $comments = $this->discussionManager->getCommentsThread($thread);
                    $moduleDescription['thread'] = $thread;
                    $moduleDescription['comments'] = $comments;

                    if ($this->authorizationChecker->isGranted(ModuleVoter::EDIT, array($module, $userModuleInvitation))) {
                        $moduleDescription['moduleForm'] = $this->moduleManager->createModuleForm($module);
                    }
                    if ($module->getPollModule() != null) {
                        if ($this->authorizationChecker->isGranted(PollProposalVoter::ADD, array($module->getPollModule(), $userModuleInvitation))) {
                            $moduleDescription['pollModuleOptions']['pollProposalAddForm'] = $this->pollProposalManager->createPollProposalAddForm($module->getPollModule());
                            $moduleDescription['pollModuleOptions']['pollProposalListAddForm'] = $this->pollProposalManager->createPollProposalListAddForm($module->getPollModule());
                        }
                    }
                    $modules[$module->getId()] = $moduleDescription;
                }
            }
        }
        return $modules;
    }

    /**
     * @param Event $event
     * @param array $modules
     * @param EventInvitation $userEventInvitation
     * @param Request $request
     * @return Response|null
     */
    public function treatModulesToDisplay(Event $event, array &$modules, EventInvitation $userEventInvitation, Request $request)
    {
        foreach ($modules as $moduleId => $moduleDescription) {
            /** @var Module $currentModule */
            $currentModule = $moduleDescription['module'];
            /** @var ModuleInvitation $userModuleEventInvitation Le ModuleInvitation de l'utilisateur connect?? pour le module courant */
            $userModuleEventInvitation = $moduleDescription['userModuleInvitation'];
            if (key_exists('moduleForm', $moduleDescription) && $moduleDescription['moduleForm'] instanceof Form) {
                /** @var Form $moduleForm */
                $moduleForm = $moduleDescription['moduleForm'];
                $moduleForm->handleRequest($request);
                if ($moduleForm->isSubmitted()) {
                    if ($request->isXmlHttpRequest()) {
                        if ($request->server->get('HTTP_REFERER') != $this->router->generate('wizardNewEventStep2', array('token' => $event->getToken()), UrlGenerator::ABSOLUTE_URL) &&
                            ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER)
                        ) {
                            // V??rification serveur de la validit?? de l'invitation
                            $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->translator->trans("event.error.message.valide_guestname_required");
                            return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                        } else if ($moduleForm->isValid()) {
                            $currentModule = $this->moduleManager->treatUpdateFormModule($moduleForm);
                            $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->translator->trans("global.success.data_saved");

                            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]["#module-" . $currentModule->getToken()] =
                                $this->moduleManager->displayModulePartial($currentModule, $userModuleEventInvitation);
                            return new AppJsonResponse($data, Response::HTTP_OK);
                        } else {
                            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#moduleEdit_form_' . $currentModule->getToken()] =
                                $this->templating->render('@App/Event/module/displayModule_form.html.twig', array(
                                    'module' => $currentModule,
                                    'moduleForm' => $moduleForm->createView(),
                                    'userModuleInvitation' => $userModuleEventInvitation
                                ));
                            return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                        }
                    } else {
                        if ($request->server->get('HTTP_REFERER') != $this->router->generate('wizardNewEventStep2', array('token' => $event->getToken()), UrlGenerator::ABSOLUTE_URL) &&
                            ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER)
                        ) {
                            // V??rification serveur de la validit?? de l'invitation
                            $this->session->getFlashBag()->add(FlashBagTypes::ERROR_TYPE, $this->translator->trans("event.error.message.valide_guestname_required"));
                            return new RedirectResponse($this->router->generate('displayEvent', array('token' => $event->getToken())));
                        } elseif ($moduleForm->isValid()) {
                            $currentModule = $this->moduleManager->treatUpdateFormModule($moduleForm);
                            return new RedirectResponse($this->router->generate('displayEvent', array('token' => $event->getToken())) . '#module-' . $currentModule->getToken());
                        }
                    }
                }
                $modules[$moduleId]['moduleForm'] = $moduleForm->createView();
            }

            //////////////////////
            // poll module case //
            //////////////////////
            if (array_key_exists('pollModuleOptions', $moduleDescription)
                && array_key_exists('pollProposalAddForm', $moduleDescription['pollModuleOptions'])
                && $moduleDescription['pollModuleOptions']['pollProposalAddForm'] instanceof Form
            ) {
                /** @var FormInterface $pollProposalAddForm */
                $pollProposalAddForm = $moduleDescription['pollModuleOptions']['pollProposalAddForm'];
                $data = array();
                $pollProposalAddForm->handleRequest($request);
                if ($pollProposalAddForm->isSubmitted()) {
                    if ($request->isXmlHttpRequest()) {
                        if ($request->server->get('HTTP_REFERER') != $this->router->generate('wizardNewEventStep2', array('token' => $event->getToken()), UrlGenerator::ABSOLUTE_URL) &&
                            ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER)
                        ) {
                            // V??rification serveur de la validit?? de l'invitation
                            $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->translator->trans("event.error.message.valide_guestname_required");
                            return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                        } else if ($pollProposalAddForm->isValid()) {
                            $pollProposal = $this->pollProposalManager->treatPollProposalForm($pollProposalAddForm, $currentModule, $userModuleEventInvitation);
                            $data[AppJsonResponse::DATA] = $this->pollProposalManager->displayPollProposalRowPartial($pollProposal, $userEventInvitation);

                            // Form reset
                            $pollProposalAddForm = $this->pollProposalManager->createPollProposalAddForm($currentModule->getPollModule());
                            $pollProposalListAddForm = $this->pollProposalManager->createPollProposalListAddForm($currentModule->getPollModule());
                            $data = $this->createPollProposalFormActionReplace($userModuleEventInvitation, $pollProposalAddForm, $pollProposalListAddForm, $moduleDescription, $data);
                            return new AppJsonResponse($data, Response::HTTP_OK);
                        } else {
                            $pollProposalListAddForm = $this->pollProposalManager->createPollProposalListAddForm($currentModule->getPollModule());
                            $data = $this->createPollProposalFormActionReplace($userModuleEventInvitation, $pollProposalAddForm, $pollProposalListAddForm, $moduleDescription, $data);
                            return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                        }
                    } else {
                        if ($request->server->get('HTTP_REFERER') != $this->router->generate('wizardNewEventStep2', array('token' => $event->getToken()), UrlGenerator::ABSOLUTE_URL) &&
                            ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER)
                        ) {
                            // V??rification serveur de la validit?? de l'invitation
                            $this->session->getFlashBag()->add(FlashBagTypes::ERROR_TYPE, $this->translator->trans("event.error.message.valide_guestname_required"));
                            return new RedirectResponse($this->router->generate('displayEvent', array('token' => $event->getToken())) . '#module-' . $currentModule->getToken());
                        } else if ($pollProposalAddForm->isValid()) {
                            $this->pollProposalManager->treatPollProposalForm($pollProposalAddForm, $currentModule, $userModuleEventInvitation);
                            return new RedirectResponse($this->router->generate('displayEvent', array('token' => $event->getToken())) . '#module-' . $currentModule->getToken());
                        }
                    }
                }
                $modules[$moduleId]['pollModuleOptions']['pollProposalAddForm'] = $pollProposalAddForm->createView();
            }

            if (array_key_exists('pollModuleOptions', $moduleDescription)
                && array_key_exists('pollProposalListAddForm', $moduleDescription['pollModuleOptions'])
                && $moduleDescription['pollModuleOptions']['pollProposalListAddForm'] instanceof Form
            ) {
                /** @var FormInterface $pollProposalListAddForm */
                $pollProposalListAddForm = $moduleDescription['pollModuleOptions']['pollProposalListAddForm'];
                $data = array();
                $pollProposalListAddForm->handleRequest($request);
                if ($pollProposalListAddForm->isSubmitted()) {
                    if ($request->isXmlHttpRequest()) {
                        if ($request->server->get('HTTP_REFERER') != $this->router->generate('wizardNewEventStep2', array('token' => $event->getToken()), UrlGenerator::ABSOLUTE_URL) &&
                            ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER)
                        ) {
                            // V??rification serveur de la validit?? de l'invitation
                            $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->translator->trans("event.error.message.valide_guestname_required");
                            return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                        } else if ($pollProposalListAddForm->isValid()) {
                            $pollProposals = $this->pollProposalManager->treatPollProposalListForm($pollProposalListAddForm, $currentModule, $userModuleEventInvitation);
                            $data[AppJsonResponse::DATA] = $this->pollProposalManager->displayPollProposalListRowPartial($pollProposals, $userEventInvitation);
                            // Form reset
                            $pollProposalAddForm = $this->pollProposalManager->createPollProposalAddForm($currentModule->getPollModule());
                            $pollProposalListAddForm = $this->pollProposalManager->createPollProposalListAddForm($currentModule->getPollModule());
                            $data = $this->createPollProposalFormActionReplace($userModuleEventInvitation, $pollProposalAddForm, $pollProposalListAddForm, $moduleDescription, $data);
                            return new AppJsonResponse($data, Response::HTTP_OK);
                        } else {
                            $pollProposalAddForm = $this->pollProposalManager->createPollProposalAddForm($currentModule->getPollModule());
                            $data = $this->createPollProposalFormActionReplace($userModuleEventInvitation, $pollProposalAddForm, $pollProposalListAddForm, $moduleDescription, $data);
                            return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                        }
                    } else {
                        if ($request->server->get('HTTP_REFERER') != $this->router->generate('wizardNewEventStep2', array('token' => $event->getToken()), UrlGenerator::ABSOLUTE_URL) &&
                            ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER)
                        ) {
                            // V??rification serveur de la validit?? de l'invitation
                            $this->session->getFlashBag()->add(FlashBagTypes::ERROR_TYPE, $this->translator->trans("event.error.message.valide_guestname_required"));
                            return new RedirectResponse($this->router->generate('displayEvent', array('token' => $event->getToken())) . '#module-' . $currentModule->getToken());
                        } else if ($pollProposalListAddForm->isValid()) {
                            $this->pollProposalManager->treatPollProposalListForm($pollProposalListAddForm, $currentModule, $userModuleEventInvitation);
                            return new RedirectResponse($this->router->generate('displayEvent', array('token' => $event->getToken())) . '#module-' . $currentModule->getToken());
                        }
                    }
                }
                $modules[$moduleId]['pollModuleOptions']['pollProposalListAddForm'] = $pollProposalListAddForm->createView();
            }
            // FIN poll module case //
        }
        // nothing to return continue the action
        return null;
    }

    /**
     * @param ModuleInvitation $userModuleEventInvitation
     * @param FormInterface $pollProposalAddForm
     * @param FormInterface|null $pollProposalListAddForm
     * @param $moduleDescription
     * @param $data
     * @return mixed
     */
    public function createPollProposalFormActionReplace(ModuleInvitation $userModuleEventInvitation, FormInterface $pollProposalAddForm, FormInterface $pollProposalListAddForm = null,
                                                        $moduleDescription, $data)
    {
        /** @var Module $currentModule */
        $currentModule = $moduleDescription['module'];
        $pollModuleOptions = array('pollProposalAddForm' => $pollProposalAddForm->createView());
        if ($pollProposalListAddForm != null) {
            $pollModuleOptions['pollProposalListAddForm'] = $pollProposalListAddForm->createView();
        }
        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#add_pp_fm_' . $currentModule->getToken() . '_formContainer'] =
            $this->templating->render('@App/Event/module/pollModulePartials/pollProposal_form.html.twig', array(
                'userModuleInvitation' => $userModuleEventInvitation,
                'pollModuleOptions' => $pollModuleOptions,
                'pp_form_modal_prefix' => "add_pp_fm_" . $currentModule->getToken(),
                'edition' => false
            ));
        return $data;
    }
}