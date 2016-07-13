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
use AppBundle\Entity\Module;
use AppBundle\Entity\module\PollModule;
use AppBundle\Entity\module\PollProposal;
use AppBundle\Form\PollProposalFormType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

class ModuleManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var FormFactory */
    private $formFactory;

    /** @var GenerateursToken */
    private $generateursToken;

    /** @var EngineInterface */
    private $templating;

    /** @var Module Le module en cours de traitement */
    private $module;

    public function __construct(EntityManager $doctrine, AuthorizationCheckerInterface $authorizationChecker, FormFactory $formFactory, GenerateursToken $generateurToken, EngineInterface $templating)
    {
        $this->entityManager = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
        $this->templating = $templating;
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
     * @param $type string
     * @return Module
     */
    public function createModule($type)
    {
        $this->module = new Module();
        $this->module->setStatus(ModuleStatus::IN_CREATION);
        $this->module->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        $this->module->setTokenEdition($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        if ($type == ModuleType::POLL_MODULE) {
            $pollModule = new PollModule();
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
        // TODO Check ?
        $this->entityManager->persist($this->module);
        $this->entityManager->flush();
        return $this->module;
    }

    public function treatAddPollProposalFormModule(Form $addPollProposalForm, Module $module)
    {
        $this->module = $module;
        /** @var PollProposal $pollProposal */
        $pollProposal = $addPollProposalForm->getData();
        if ($this->module == null or $pollProposal == null) {
            return null;
        }
        $pollProposal->setPollModule($this->module->getPollModule());
        $this->entityManager->persist($pollProposal);
        $this->entityManager->flush();
        return $pollProposal;
    }

    /**
     * @param Module $module Le module à afficher
     * @param $allowEdit boolean "true" si le module est éditable
     * @param $moduleForm FormInterface Formulaire
     * @param Request $request
     * @return string La vue HTML sous forme de string
     */
    public function displayModulePartial(Module $module, $allowEdit, FormInterface $moduleForm)
    {
        if ($module->getPollModule() != null) {
            return $this->templating->render("@App/Event/module/displayPollModule.html.twig", array(
                "module" => $module,
                "allowEdit" => $allowEdit,
                'moduleForm' => ($moduleForm != null ? $moduleForm->createView() : null),
                'addPollProposalForm' => $this->createAddPollProposalForm($module)->createView()
            ));
        } elseif ($module->getExpenseModule() != null) {
            return $this->templating->render("@App/Event/module/displayExpenseModule.html.twig", [
                "module" => $module,
                "allowEdit" => $allowEdit,
                'moduleForm' => ($moduleForm != null ? $moduleForm->createView() : null)
            ]);
        } else {
            return $this->templating->render("@App/Event/module/displayModule.html.twig", [
                "module" => $module,
                "allowEdit" => $allowEdit,
                'moduleForm' => ($moduleForm != null ? $moduleForm->createView() : null)
            ]);
        }
    }


    /**
     * @param PollProposal $pollProposal
     * @return string
     */
    public function displayPollProposalRowPartial(PollProposal $pollProposal)
    {
        return $this->templating->render("@App/Event/module/pollModulePartials/pollProposalGuestResponseRowDisplay.html.twig", array(
            "pollProposal" => $pollProposal,
            'moduleInvitations' => $pollProposal->getPollModule()->getModule()->getModuleInvitations()
        ));
    }

    /**
     * @param Module $module
     * @return FormInterface
     */
    public function createAddPollProposalForm(Module $module)
    {
        return $this->formFactory->createNamed("add_poll_proposal_form_" . $module->getToken(), PollProposalFormType::class, new PollProposal());
    }
}