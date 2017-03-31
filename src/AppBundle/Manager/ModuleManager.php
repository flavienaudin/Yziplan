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
use AppBundle\Form\Module\ModuleType;
use AppBundle\Security\ModuleVoter;
use AppBundle\Utils\enum\ModuleStatus;
use AppBundle\Utils\enum\ModuleType as EnumModuleType;
use AppBundle\Utils\enum\PollModuleType;
use AppBundle\Utils\enum\PollModuleVotingType;
use Doctrine\Common\Collections\ArrayCollection;
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

    /** @var TranslatorInterface $translator */
    private $translator;

    /** @var Module Le module en cours de traitement */
    private $module;

    public function __construct(EntityManager $doctrine, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, FormFactoryInterface $formFactory,
                                GenerateursToken $generateurToken, EngineInterface $templating, ModuleInvitationManager $moduleInvitationManager, PollProposalManager $pollProposalManager,
                                DiscussionManager $discussionManager, TranslatorInterface $translator)
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
        $this->translator = $translator;
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

        $this->initializePollModule($type, $subtype);

        $moduleInvitationCreator = $this->moduleInvitationManager->initializeModuleInvitation($this->module, $creatorEventInvitation, true);
        $moduleInvitationCreator->setCreator(true);
        $event->addModule($this->module);
        return $this->module;
    }


    /**
     * Create a module and set required data.
     * @param $type
     * @param $subtype
     * @param EventInvitation $creatorEventInvitation The user's eventInvitation to set the module creator
     * @return Module The module added to the event
     */
    public function initializePollModule($type, $subtype, $module = null)
    {
        if ($module != null) {
            $this->module = $module;
        }

        if ($type == EnumModuleType::POLL_MODULE) {
            $pollModule = new PollModule();
            $pollModule->setVotingType(PollModuleVotingType::YES_NO_MAYBE);

            $pollElements = new ArrayCollection();

            if ($subtype == PollModuleType::WHEN) {
                $this->module->setName($this->translator->trans("pollmodule.add_link.when"));
                $this->module->setStatus(ModuleStatus::IN_ORGANIZATION);
                $pollModule->setType(PollModuleType::WHEN);
            } elseif ($subtype == PollModuleType::WHAT) {
                $this->module->setName($this->translator->trans("pollmodule.add_link.what"));
                $this->module->setStatus(ModuleStatus::IN_ORGANIZATION);
                $pollModule->setType(PollModuleType::WHAT);
            } elseif ($subtype == PollModuleType::WHERE) {
                $this->module->setName($this->translator->trans("pollmodule.add_link.where"));
                $this->module->setStatus(ModuleStatus::IN_ORGANIZATION);
                $pollModule->setType(PollModuleType::WHERE);
            } elseif ($subtype == PollModuleType::WHO_BRINGS_WHAT) {
                $this->module->setName($this->translator->trans("pollmodule.add_link.whobringswhat"));
                $this->module->setStatus(ModuleStatus::IN_ORGANIZATION);
                $pollModule->setVotingType(PollModuleVotingType::AMOUNT);
                $pollModule->setType(PollModuleType::WHO_BRINGS_WHAT);
            } elseif ($subtype == PollModuleType::ACTIVITY) {
                $this->module->setName($this->translator->trans("pollmodule.add_link.activity"));
                $this->module->setStatus(ModuleStatus::IN_ORGANIZATION);
                $pollModule->setVotingType(PollModuleVotingType::RANKING);
                $pollModule->setType(PollModuleType::ACTIVITY);
            }
            $pollModule->addPollElements($pollElements);

            $this->module->setPollModule($pollModule);
        }
        return $this->module;
    }

    /**
     * Supprime le module de son événement en changeant le status du module ) "DELETED"
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
     * @param Module $originalModule Module à dupliquer
     * @return Module Le module résultant de la dupliquation
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
            $duplicatedModule->setInvitationOnly($originalModule->isInvitationOnly());
            $duplicatedModule->setGuestsCanInvite($originalModule->isGuestsCanInvite());

            if ($originalModule->getPaymentModule() != null) {
                // TODO implementer la duplication du PaymentModule
            }

            if (($originalPollModule = $originalModule->getPollModule()) != null) {
                $duplicatedPollModule = new PollModule();
                $duplicatedPollModule->setVotingType($originalPollModule->getVotingType());
                $duplicatedPollModule->setType($originalPollModule->getType());

                /** @var PollProposal $originalPollProposal */
                foreach ($originalPollModule->getPollProposals() as $originalPollProposal) {
                    if (!$originalPollProposal->isDeleted()) {
                        $duplicatedPollProposal = new PollProposal();
                        $duplicatedPollProposal->setDeleted(false);
                        $duplicatedPollProposal->setDescription($originalPollProposal->getDescription());
                        $duplicatedPollModule->addPollProposal($duplicatedPollProposal);
                        $duplicatedPollProposal->setValString($originalPollProposal->getValString());
                        $duplicatedPollProposal->setValText($originalPollProposal->getValText());
                        $duplicatedPollProposal->setValInteger($originalPollProposal->getValInteger());
                        $duplicatedPollProposal->setValDatetime($originalPollProposal->getValDatetime());
                        $duplicatedPollProposal->setTime($originalPollProposal->hasTime());
                        $duplicatedPollProposal->setValEndDatetime($originalPollProposal->getValEndDatetime());
                        $duplicatedPollProposal->setEndDate($originalPollProposal->hasEndDate());
                        $duplicatedPollProposal->setEndTime($originalPollProposal->hasEndTime());
                        $duplicatedPollProposal->setValGooglePlaceId($originalPollProposal->getValGooglePlaceId());

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

        if ($this->module->getStatus() == ModuleStatus::IN_CREATION && !empty($this->module->getName())) {
            $this->module->setStatus(ModuleStatus::IN_ORGANIZATION);
        } elseif ($this->module->getStatus() == ModuleStatus::IN_ORGANIZATION && empty($this->module->getName())) {
            $this->module->setStatus(ModuleStatus::IN_CREATION);
        }

        // TODO faire des vérifications/traitement sur les données

        $this->entityManager->persist($this->module);
        $this->entityManager->flush();
        return $this->module;
    }

    /**
     * @param Module $module Le module à afficher
     * @param ModuleInvitation|null $userModuleInvitation
     * @return string La vue HTML sous forme de string
     */
    public function displayModulePartial(Module $module, ModuleInvitation $userModuleInvitation = null)
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
            // TODO Check authorization to "AddPollProposal"
            $pollModuleOptions['pollProposalAddForm'] = $this->pollProposalManager->createPollProposalAddForm($module->getPollModule())->createView();
            $listView = $this->pollProposalManager->createPollProposalListAddForm($module->getPollModule());
            if ($listView != null) {
                $pollModuleOptions['pollProposalListAddForm'] = $listView->createView();
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
     * @param Module $module Le module à afficher
     * @return string La vue HTML sous forme de string
     */
    public function displayPollModuleResultTable(Module $module)
    {
        return $this->templating->render("@App/Event/module/pollModulePartials/pollProposalGuestResponseTableDisplay.html.twig", array(
            "module" => $module,
            "moduleInvitations" => $module->getModuleInvitations()
        ));
    }


}