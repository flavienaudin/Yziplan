<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 29/06/2016
 * Time: 10:22
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalResponse;
use AppBundle\Form\Module\ModuleType;
use AppBundle\Security\ModuleVoter;
use AppBundle\Security\PollProposalVoter;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\InvitationRule;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use AppBundle\Utils\enum\ModuleStatus;
use AppBundle\Utils\enum\ModuleType as EnumModuleType;
use AppBundle\Utils\enum\PollModuleType;
use AppBundle\Utils\enum\PollModuleVotingType;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ModuleManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var  TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var FormFactory */
    private $formFactory;

    /** @var GenerateursToken */
    private $generateursToken;

    /** @var EngineInterface */
    private $templating;

    /** @var ModuleInvitationManager */
    private $moduleInvitationManager;

    /** @var DiscussionManager $discussionManager */
    private $discussionManager;

    /** @var PollProposalManager */
    private $pollProposalManager;

    /** @var PollProposalResponseManager */
    private $pollProposalResponseManager;

    /** @var TranslatorInterface $translator */
    private $translator;

    /** @var NotificationManager */
    private $notificationManager;

    /** @var Module Le module en cours de traitement */
    private $module;

    public function __construct(EntityManager $doctrine, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, FormFactoryInterface $formFactory,
                                GenerateursToken $generateurToken, EngineInterface $templating, ModuleInvitationManager $moduleInvitationManager, PollProposalManager $pollProposalManager,
                                PollProposalResponseManager $pollProposalResponseManager, DiscussionManager $discussionManager, TranslatorInterface $translator,
                                NotificationManager $notificationManager)
    {
        $this->entityManager = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
        $this->templating = $templating;
        $this->moduleInvitationManager = $moduleInvitationManager;
        $this->discussionManager = $discussionManager;
        $this->pollProposalManager = $pollProposalManager;
        $this->pollProposalResponseManager = $pollProposalResponseManager;
        $this->translator = $translator;
        $this->notificationManager = $notificationManager;
    }

    /**
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param Module $module
     * @return ModuleManager
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    public function retrieveModuleByToken($token)
    {
        $moduleRepo = $this->entityManager->getRepository(Module::class);
        $this->module = $moduleRepo->findOneBy(array('token' => $token));
        return $this->module;
    }

    /**
     * Create a module and set required data.
     * @param Event $event The event which is added the module.
     * @param $type
     * @param $subtype
     * @param EventInvitation $creatorEventInvitation The user's eventInvitation to set the module creator
     * @return Module The module added to the event
     */
    public function createModule(Event $event, $type, $subtype, EventInvitation $creatorEventInvitation)
    {
        $this->module = new Module();
        $this->module->setStatus(ModuleStatus::IN_CREATION);
        $this->module->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));

        $this->initializeSubModule($type, $subtype);

        $moduleInvitationCreator = $this->moduleInvitationManager->initializeModuleInvitation($this->module, $creatorEventInvitation, true);
        $moduleInvitationCreator->setCreator(true);
        $moduleInvitationCreator->setStatus(ModuleInvitationStatus::INVITED);
        $event->addModule($this->module);
        return $this->module;
    }


    /**
     * Create a module and set required data.
     * @param $type
     * @param $subtype
     * @param $module Module (optionel) le module concern??
     * @return Module The module added to the event
     */
    public function initializeSubModule($type, $subtype, $module = null)
    {
        if ($module != null) {
            $this->module = $module;
        }

        if ($type == EnumModuleType::POLL_MODULE) {
            $pollModule = new PollModule();
            $pollModule->setVotingType(PollModuleVotingType::YES_NO_MAYBE);

            if ($subtype == PollModuleType::WHEN) {
                $this->module->setName($this->translator->trans("pollmodule.add_link.when"));
                $pollModule->setType(PollModuleType::WHEN);
            } elseif ($subtype == PollModuleType::WHAT) {
                $this->module->setName($this->translator->trans("pollmodule.add_link.what"));
                $pollModule->setType(PollModuleType::WHAT);
            } elseif ($subtype == PollModuleType::WHERE) {
                $this->module->setName($this->translator->trans("pollmodule.add_link.where"));
                $pollModule->setType(PollModuleType::WHERE);
            } elseif ($subtype == PollModuleType::WHO_BRINGS_WHAT) {
                $this->module->setName($this->translator->trans("pollmodule.add_link.whobringswhat"));
                $pollModule->setVotingType(PollModuleVotingType::AMOUNT);
                $pollModule->setType(PollModuleType::WHO_BRINGS_WHAT);
            } elseif ($subtype == PollModuleType::ACTIVITY) {
                $this->module->setName($this->translator->trans("pollmodule.add_link.activity"));
                $pollModule->setVotingType(PollModuleVotingType::SCORING);
                $pollModule->setType(PollModuleType::ACTIVITY);
            }
            $this->module->setPollModule($pollModule);
        }
        return $this->module;
    }

    /**
     * @param EventInvitation $modulePublisherEventInvitation Le ModuleInvitation du publieur
     * @param Module|null $module Le module concern??
     * @param bool $generateNotifications Si true Alors les notifications sont g??n??r??es
     * @return bool true si l'op??ration s'est bien d??roul??, false sinon
     */
    public function publishModule(EventInvitation $modulePublisherEventInvitation, Module $module = null, $generateNotifications = true)
    {
        if ($module != null) {
            $this->module = $module;
        }
        if ($this->module != null) {
            $this->module->setStatus(ModuleStatus::IN_ORGANIZATION);
            if ($this->module->getInvitationRule() == InvitationRule::EVERYONE) {
                // Seul le cas InvitationRule::EVERYONE est trait?? car dans les autres cas, c'est lors du param??trage des ModuleInvitation que le statut est renseign??
                /** @var ModuleInvitation $moduleInvitation */
                foreach ($this->module->getModuleInvitations() as $moduleInvitation) {
                    if ($moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::CANCELLED
                        && $moduleInvitation->getStatus() != ModuleInvitationStatus::EXCLUDED
                    ) {
                        // EventInvitation annul??e
                        $moduleInvitation->setStatus(ModuleInvitationStatus::NOT_INVITED);
                        // excluded reste excluded
                    } else {
                        $moduleInvitation->setStatus(ModuleInvitationStatus::INVITED);
                    }
                }
            }
            $this->entityManager->persist($this->module);
            $this->entityManager->flush();

            if ($generateNotifications) {
                // Cr??ation d'un notification pour chaque invit??
                $this->notificationManager->createAddModuleNotifications($module, $modulePublisherEventInvitation);
            }
            return true;
        }
        return false;
    }

    /**
     * Supprime le module de son ??v??nement en changeant le status du module ) "DELETED"
     */
    public function removeModule()
    {
        if ($this->module != null) {
            $this->module->setStatus(ModuleStatus::DELETED);
        }
        $this->entityManager->persist($this->module);
        $this->entityManager->flush();
        return $this->module;
    }

    /**
     * @param Module $originalModule Module ?? dupliquer
     * @return Module Le module r??sultant de la dupliquation
     */
    public function duplicateModule(Module $originalModule)
    {
        if ($originalModule != null) {
            $this->module = $originalModule;
        }
        if ($this->module != null) {
            $duplicatedModule = new Module();
            $duplicatedModule->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
            $duplicatedModule->setName($originalModule->getName());
            $duplicatedModule->setDescription($originalModule->getDescription());
            $duplicatedModule->setOrderIndex($originalModule->getOrderIndex());
            $duplicatedModule->setStatus(ModuleStatus::IN_ORGANIZATION);
            $duplicatedModule->setResponseDeadline($originalModule->getResponseDeadline());
            $duplicatedModule->setGuestsCanInvite($originalModule->isGuestsCanInvite());
            $duplicatedModule->setInvitationRule($originalModule->getInvitationRule());

            if ($originalModule->getPaymentModule() != null) {
                // TODO implementer la duplication du PaymentModule
            }

            if (($originalPollModule = $originalModule->getPollModule()) != null) {
                $duplicatedPollModule = new PollModule();
                $duplicatedPollModule->setVotingType($originalPollModule->getVotingType());
                $duplicatedPollModule->setType($originalPollModule->getType());
                $duplicatedPollModule->setGuestsCanAddProposal($originalPollModule->isGuestsCanAddProposal());

                /** @var PollProposal $originalPollProposal */
                foreach ($originalPollModule->getPollProposals() as $originalPollProposal) {
                    if (!$originalPollProposal->isDeleted()) {
                        $duplicatedPollProposal = new PollProposal();
                        $duplicatedPollProposal->setDeleted(false);
                        $duplicatedPollProposal->setDescription($originalPollProposal->getDescription());

                        $duplicatedPollProposal->setValString($originalPollProposal->getValString());
                        $duplicatedPollProposal->setValText($originalPollProposal->getValText());
                        $duplicatedPollProposal->setValInteger($originalPollProposal->getValInteger());
                        $duplicatedPollProposal->setValDatetime($originalPollProposal->getValDatetime());
                        $duplicatedPollProposal->setTime($originalPollProposal->hasTime());
                        $duplicatedPollProposal->setValEndDatetime($originalPollProposal->getValEndDatetime());
                        $duplicatedPollProposal->setEndDate($originalPollProposal->hasEndDate());
                        $duplicatedPollProposal->setEndTime($originalPollProposal->hasEndTime());
                        $duplicatedPollProposal->setValGooglePlaceId($originalPollProposal->getValGooglePlaceId());

                        $duplicatedPollModule->addPollProposal($duplicatedPollProposal);

                        if ($originalPollProposal->getPictureFile() != null) {
                            $originalFile = $originalPollProposal->getPictureFile();
                            $tempFileCopyName = str_replace($originalFile->getExtension(), 'dup.' . $originalFile->getExtension(), $originalFile->getFilename());
                            $tempFileCopyPathname = $originalPollProposal->getPictureFile()->getPath() . '/' . $tempFileCopyName;
                            if (copy($originalPollProposal->getPictureFile()->getPathname(), $tempFileCopyPathname)) {
                                $newFile = new UploadedFile($tempFileCopyPathname, $tempFileCopyName, $originalFile->getMimeType(), $originalFile->getSize(), null, true);
                                $duplicatedPollProposal->setPictureFile($newFile);
                            }
                        }
                    }
                }

                $duplicatedModule->setPollModule($duplicatedPollModule);
            } elseif (($originalExpenseModule = $originalModule->getExpenseModule()) != null) {
                // TODO implementer la duplication de l'expenseModule
            }

            return $duplicatedModule;
        } else {
            return null;
        }
    }

    /**
     * @param Module $module
     * @return FormInterface
     */
    public function createModuleForm(Module $module)
    {
        return $this->formFactory->createNamed("module_form_" . $module->getToken(), ModuleType::class, $module);
    }

    /**
     * @param Form $moduleForm
     * @return Module
     */
    public function treatUpdateFormModule(Form $moduleForm)
    {
        $this->module = $moduleForm->getData();

        /*--  Gestion des invitations au module  --*/
        $moduleInvitationsSelection = $moduleForm->get('moduleInvitationSelected')->getData();
        /** @var ModuleInvitation $moduleInvitation */
        foreach ($this->module->getModuleInvitations() as $moduleInvitation) {
            if ($moduleInvitation->isCreator()) {
                $moduleInvitation->setStatus(ModuleInvitationStatus::INVITED);
            } elseif ($this->module->getInvitationRule() == InvitationRule::EVERYONE) {
                if ($moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::CANCELLED) {
                    $moduleInvitation->setStatus(ModuleInvitationStatus::NOT_INVITED);
                } else {
                    $moduleInvitation->setStatus(ModuleInvitationStatus::INVITED);
                }
            } elseif ($this->module->getInvitationRule() == InvitationRule::NONE_EXCEPT) {
                if ($moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::CANCELLED) {
                    $moduleInvitation->setStatus(ModuleInvitationStatus::NOT_INVITED);
                } else {
                    if (in_array($moduleInvitation, $moduleInvitationsSelection)) {
                        $moduleInvitation->setStatus(ModuleInvitationStatus::INVITED);
                    } else {
                        $moduleInvitation->setStatus(ModuleInvitationStatus::EXCLUDED);
                    }
                }
            }
            $this->entityManager->persist($moduleInvitation);
        }

        /*--  IF PollModule  --*/
        if ($this->module->getPollModule() != null && (($pollModuleForm = $moduleForm->get('pollModule')) != null)) {
            /*--  Gestion du VotingType  --*/
            $oldVotingType = $pollModuleForm->get('oldVotingType')->getData();
            if ($oldVotingType !== $this->module->getPollModule()->getVotingType()) {
                $moduleInvitationToWarn = $this->changePollModuleVotingType($oldVotingType);

                if (count($moduleInvitationToWarn) > 0) {
                    $this->notificationManager->createChangPollModuleVotingTypeNotifications($this->module, $moduleInvitationToWarn);
                }
            }
        }

        $this->entityManager->persist($this->module);
        $this->entityManager->flush();
        return $this->module;
    }

    /**
     * @param $oldVotingType string L'ancien type de vote du sondage
     * @return Collection|array Les ModuleInvitation ?? pr??venir d'un changement de vote
     */
    private function changePollModuleVotingType($oldVotingType)
    {
        $moduleInvitationsToWarn = array();
        $pollModule = $this->module->getPollModule();
        $newVotingType = $pollModule->getVotingType();

        if ($newVotingType == PollModuleVotingType::AMOUNT || $oldVotingType == PollModuleVotingType::AMOUNT) {
            return $this->pollProposalResponseManager->resetPollProposalResponse($pollModule);
        }

        if ($newVotingType === PollModuleVotingType::SCORING) {
            /** @var ModuleInvitation $moduleInvitation */
            foreach ($this->module->getModuleInvitations() as $moduleInvitation) {
                /** @var PollProposalResponse $pollProposalResponse */
                foreach ($moduleInvitation->getPollProposalResponses() as $pollProposalResponse) {
                    if ($pollProposalResponse->getAnswer() !== null) {
                        if ($oldVotingType == PollModuleVotingType::YES_NO_MAYBE || $oldVotingType == PollModuleVotingType::YES_NO) {
                            if ($pollProposalResponse->getAnswer() === \AppBundle\Utils\enum\PollProposalResponse::YES) {
                                $pollProposalResponse->setAnswer(5);
                            } elseif ($pollProposalResponse->getAnswer() === \AppBundle\Utils\enum\PollProposalResponse::NO) {
                                $pollProposalResponse->setAnswer(1);
                            } else {
                                $pollProposalResponse->setAnswer(3);
                            }
                        }
                    }
                }
            }
        } elseif ($oldVotingType === PollModuleVotingType::SCORING) {
            /** @var ModuleInvitation $moduleInvitation */
            foreach ($this->module->getModuleInvitations() as $moduleInvitation) {
                /** @var PollProposalResponse $pollProposalResponse */
                foreach ($moduleInvitation->getPollProposalResponses() as $pollProposalResponse) {
                    if ($pollProposalResponse->getAnswer() != null) {
                        if ($pollProposalResponse->getAnswer() > 4) {
                            $pollProposalResponse->setAnswer(\AppBundle\Utils\enum\PollProposalResponse::YES);
                        } elseif ($pollProposalResponse->getAnswer() == 1) {
                            $pollProposalResponse->setAnswer(\AppBundle\Utils\enum\PollProposalResponse::NO);
                        } else {
                            if ($newVotingType == PollModuleVotingType::YES_NO_MAYBE) {
                                $pollProposalResponse->setAnswer(\AppBundle\Utils\enum\PollProposalResponse::MAYBE);
                            } else {
                                if ($pollProposalResponse->getAnswer() == 2) {
                                    $pollProposalResponse->setAnswer(\AppBundle\Utils\enum\PollProposalResponse::NO);
                                } else {
                                    $pollProposalResponse->setAnswer(null);
                                    if ($moduleInvitation->getStatus() == ModuleInvitationStatus::INVITED && $moduleInvitation->getEventInvitation()->getStatus() != EventInvitationStatus::CANCELLED) {
                                        $moduleInvitationsToWarn[$moduleInvitation->getId()] = $moduleInvitation;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } elseif ($oldVotingType == PollModuleVotingType::YES_NO_MAYBE && $newVotingType == PollModuleVotingType::YES_NO) {
            /** @var ModuleInvitation $moduleInvitation */
            foreach ($this->module->getModuleInvitations() as $moduleInvitation) {
                /** @var PollProposalResponse $pollProposalResponse */
                foreach ($moduleInvitation->getPollProposalResponses() as $pollProposalResponse) {
                    if ($pollProposalResponse->getAnswer() != null) {
                        if ($pollProposalResponse->getAnswer() === \AppBundle\Utils\enum\PollProposalResponse::MAYBE) {
                            $pollProposalResponse->setAnswer(null);
                            if ($moduleInvitation->getStatus() == ModuleInvitationStatus::INVITED && $moduleInvitation->getEventInvitation()->getStatus() != EventInvitationStatus::CANCELLED) {
                                $moduleInvitationsToWarn[$moduleInvitation->getId()] = $moduleInvitation;
                            }
                        }
                    }
                }
            }
        }
        return $moduleInvitationsToWarn;
    }


    /**
     * @param Module $module Le module ?? afficher
     * @param ModuleInvitation|null $userModuleInvitation
     * @return string La vue HTML sous forme de string
     */
    public
    function displayModulePartial(Module $module, ModuleInvitation $userModuleInvitation = null)
    {
        $moduleForm = null;
        if ($this->authorizationChecker->isGranted(ModuleVoter::EDIT, array($module, $userModuleInvitation))) {
            /** @var FormInterface $moduleForm */
            $moduleForm = $this->createModuleForm($module);
        }
        $thread = $module->getCommentThread();
        if ($thread != null) {
            $comments = $this->discussionManager->getCommentsThread($thread);
        } else {
            $comments = [];
        }
        if ($module->getPollModule() != null) {
            $pollModuleOptions = array();
            if ($this->authorizationChecker->isGranted(PollProposalVoter::ADD, array($module->getPollModule(), $userModuleInvitation))) {
                $pollModuleOptions['pollProposalAddForm'] = $this->pollProposalManager->createPollProposalAddForm($module->getPollModule())->createView();
                $listView = $this->pollProposalManager->createPollProposalListAddForm($module->getPollModule());
                if ($listView != null) {
                    $pollModuleOptions['pollProposalListAddForm'] = $listView->createView();
                }
            }
            return $this->templating->render("@App/Event/module/displayPollModule.html.twig", array(
                "module" => $module,
                'moduleForm' => ($moduleForm != null ? $moduleForm->createView() : null),
                'pollModuleOptions' => $pollModuleOptions,
                'userModuleInvitation' => $userModuleInvitation,
                'thread' => $thread, 'comments' => $comments
            ));
        } elseif ($module->getExpenseModule() != null) {
            return $this->templating->render("@App/Event/module/displayExpenseModule.html.twig", [
                "module" => $module,
                'moduleForm' => ($moduleForm != null ? $moduleForm->createView() : null),
                'userModuleInvitation' => $userModuleInvitation,
                'thread' => $thread, 'comments' => $comments
            ]);
        } else {
            return $this->templating->render("@App/Event/module/displayModule.html.twig", [
                "module" => $module,
                'moduleForm' => ($moduleForm != null ? $moduleForm->createView() : null),
                'userModuleInvitation' => $userModuleInvitation,
                'thread' => $thread, 'comments' => $comments
            ]);
        }
    }

    /**
     * @param Module $module Le module ?? afficher
     * @return string La vue HTML sous forme de string
     */
    public
    function displayPollModuleResultTable(Module $module)
    {
        return $this->templating->render("@App/Event/module/pollModulePartials/pollProposalGuestResponseTableDisplay.html.twig", array(
            "module" => $module,
            "moduleInvitations" => $module->getModuleInvitations()
        ));
    }


}