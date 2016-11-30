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
use AppBundle\Entity\Module\PollElement;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Form\Module\ModuleType;
use AppBundle\Security\ModuleVoter;
use AppBundle\Utils\enum\ModuleStatus;
use AppBundle\Utils\enum\ModuleType as EnumModuleType;
use AppBundle\Utils\enum\PollElementType;
use AppBundle\Utils\enum\PollModuleVotingType;
use AppBundle\Utils\enum\PollModuleType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

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

    /** @var PollProposalManager */
    private $pollProposalManager;

    /** @var Module Le module en cours de traitement */
    private $module;

    public function __construct(EntityManager $doctrine, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, FormFactoryInterface $formFactory,
                                GenerateursToken $generateurToken, EngineInterface $templating, ModuleInvitationManager $moduleInvitationManager, PollProposalManager $pollProposalManager)
    {
        $this->entityManager = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
        $this->templating = $templating;
        $this->moduleInvitationManager = $moduleInvitationManager;
        $this->pollProposalManager = $pollProposalManager;
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
        if ($type == EnumModuleType::POLL_MODULE) {
            $pollModule = new PollModule();
            $pollElement = new PollElement();
            $pollElement->setName($subtype);
            $pollElement->setOrderIndex(0);

            // default config
            $pollElement->setType(PollElementType::STRING);
            $pollModule->setVotingType(PollModuleVotingType::YES_NO_MAYBE);

            if ($subtype == PollModuleType::WHEN) {
                $this->module->setName("pollmodule.add_link.when");
                $pollElement->setType(PollElementType::DATETIME);
            } elseif ($subtype == PollModuleType::WHAT) {
                $this->module->setName("pollmodule.add_link.what");
                $pollElement->setType(PollElementType::STRING);
            } elseif ($subtype == PollModuleType::WHERE) {
                $this->module->setName("pollmodule.add_link.where");
                $pollElement->setType(PollElementType::STRING);

                // to stock google place id
                $pollElement_place_id = new PollElement();
                $pollElement_place_id->setType(PollElementType::HIDDEN);
                $pollElement_place_id->setName(PollElementType::HIDDEN);
                $pollElement_place_id->setOrderIndex(1);
                $pollModule->addPollElement($pollElement_place_id);
            } elseif ($subtype == PollModuleType::WHO_BRINGS_WHAT) {
                $this->module->setName("pollmodule.add_link.whobringswhat");
                $pollElement->setType(PollElementType::STRING);
                $pollModule->setVotingType(PollModuleVotingType::AMOUNT);
            }
            $pollModule->addPollElement($pollElement);


            $this->module->setPollModule($pollModule);

        }
        $moduleInvitationCreator = $this->moduleInvitationManager->initializeModuleInvitation($this->module, $creatorEventInvitation, true);
        $moduleInvitationCreator->setCreator(true);
        $event->addModule($this->module);
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
        if ($this->authorizationChecker->isGranted(ModuleVoter::EDIT, $userModuleInvitation)) {
            /** @var FormInterface $moduleForm */
            $moduleForm = $this->createModuleForm($module);
        }
        if ($module->getPollModule() != null) {
            // TODO Check authorization to "AddPollProposal"
            return $this->templating->render("@App/Event/module/displayPollModule.html.twig", array(
                "module" => $module,
                'moduleForm' => ($moduleForm != null ? $moduleForm->createView() : null),
                'pollProposalAddForm' => $this->pollProposalManager->createPollProposalAddForm($module->getPollModule(), $userModuleInvitation)->createView(),
                'userModuleInvitation' => $userModuleInvitation
            ));
        } elseif ($module->getExpenseModule() != null) {
            return $this->templating->render("@App/Event/module/displayExpenseModule.html.twig", [
                "module" => $module,
                'moduleForm' => ($moduleForm != null ? $moduleForm->createView() : null),
                'userModuleInvitation' => $userModuleInvitation
            ]);
        } else {
            return $this->templating->render("@App/Event/module/displayModule.html.twig", [
                "module" => $module,
                'moduleForm' => ($moduleForm != null ? $moduleForm->createView() : null),
                'userModuleInvitation' => $userModuleInvitation
            ]);
        }
    }


}