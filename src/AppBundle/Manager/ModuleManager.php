<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 29/06/2016
 * Time: 10:22
 */

namespace AppBundle\Manager;


use AppBundle\Entity\enum\ModuleStatus;
use AppBundle\Entity\enum\ModuleType;
use AppBundle\Entity\enum\PollModuleSortingType;
use AppBundle\Entity\enum\PollProposalElementType;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvitation;
use AppBundle\Entity\Module;
use AppBundle\Entity\module\PollModule;
use AppBundle\Entity\module\PollProposal;
use AppBundle\Entity\module\PollProposalElement;
use AppBundle\Entity\ModuleInvitation;
use AppBundle\Form\ModuleFormType;
use AppBundle\Form\PollProposalFormType;
use AppBundle\Security\ModuleVoter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
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

    /** @var Module Le module en cours de traitement */
    private $module;

    public function __construct(EntityManager $doctrine, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, FormFactoryInterface $formFactory,
                                GenerateursToken $generateurToken, EngineInterface $templating, ModuleInvitationManager $moduleInvitationManager)
    {
        $this->entityManager = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
        $this->templating = $templating;
        $this->moduleInvitationManager = $moduleInvitationManager;
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
     * @param EventInvitation $creatorEventInvitation The user's eventInvitation to set the module creator
     * @return Module The module added to the event
     */
    public function createModule(Event $event, $type, EventInvitation $creatorEventInvitation)
    {
        $this->module = new Module();
        $this->module->setStatus(ModuleStatus::IN_CREATION);
        $this->module->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        $this->module->setTokenEdition($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        if ($type == ModuleType::POLL_MODULE) {
            $pollModule = new PollModule();
            $pollModule->setSortingType(PollModuleSortingType::YES_NO_MAYBE);
            $this->module->setPollModule($pollModule);
        }
        $moduleInvitation = $this->moduleInvitationManager->initializeModuleInvitation($this->module, $creatorEventInvitation, true);
        $this->module->setCreator($moduleInvitation);
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
        return $this->formFactory->createNamed("module_form_" . $module->getTokenEdition(), ModuleFormType::class, $module);
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

    public function treatAddPollProposalFormModule(FormInterface $addPollProposalForm, Module $module, Request $request = null)
    {
        $this->module = $module;
        /** @var PollProposal $pollProposal */
        $pollProposal = $addPollProposalForm->getData();
        if ($this->module == null or $pollProposal == null) {
            return null;
        }
        $pollProposal->setPollModule($this->module->getPollModule());

        if($addPollProposalForm->has('strPPElts')){
            $strPPElts = $addPollProposalForm->get('strPPElts')->getData();
            dump($strPPElts);
            foreach ($strPPElts as $key => $value){
                $newPPE = new PollProposalElement();
                $newPPE->setName($key);
                $newPPE->setType(PollProposalElementType::STRING);
                $newPPE->setValString($value);
                $pollProposal->addPollProposalElement($newPPE);
            }
        }
        if($addPollProposalForm->has('intPPElts')){
            $intPPElts = $addPollProposalForm->get('intPPElts')->getData();
            dump($intPPElts);
            foreach ($intPPElts as $key => $value){
                $newPPE = new PollProposalElement();
                $newPPE->setName($key);
                $newPPE->setType(PollProposalElementType::INTEGER);
                $newPPE->setValString($value);
                $pollProposal->addPollProposalElement($newPPE);
            }
        }

        $this->entityManager->persist($pollProposal);
        $this->entityManager->flush();
        return $pollProposal;
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
        if ($module->getPollModule() != null) {
            // TODO Check authorization to "AddPollProposal"
            return $this->templating->render("@App/Event/module/displayPollModule.html.twig", array(
                "module" => $module,
                'moduleForm' => ($moduleForm != null ? $moduleForm->createView() : null),
                'addPollProposalForm' => $this->createAddPollProposalForm($module, $userModuleInvitation)->createView(),
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


    /**
     * @param PollProposal $pollProposal
     * @param EventInvitation $userEventInvitation
     * @return string
     */
    public function displayPollProposalRowPartial(PollProposal $pollProposal, EventInvitation $userEventInvitation)
    {
        $userModuleInvitation = null ;
        if ($userEventInvitation != null) {
            $userModuleInvitation = $userEventInvitation->getModuleInvitationForModule($pollProposal->getPollModule()->getModule());
        }
        return $this->templating->render("@App/Event/module/pollModulePartials/pollProposalGuestResponseRowDisplay.html.twig", array(
            'pollProposal' => $pollProposal,
            'moduleInvitations' => $pollProposal->getPollModule()->getModule()->getModuleInvitations(),
            'userModuleInvitation' => $userModuleInvitation
        ));
    }

    /**
     * @param Module $module
     * @param ModuleInvitation $userModuleInvitation
     * @return FormInterface
     */
    public function createAddPollProposalForm(Module $module, ModuleInvitation $userModuleInvitation = null)
    {
        $newPollProposal = new PollProposal();
        $newPollProposal->setCreator($userModuleInvitation);
        return $this->formFactory->createNamed("add_poll_proposal_form_" . $module->getToken(), PollProposalFormType::class, $newPollProposal);
    }
}