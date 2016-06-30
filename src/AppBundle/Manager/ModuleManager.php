<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 29/06/2016
 * Time: 10:22
 */

namespace AppBundle\Manager;


use AppBundle\Entity\enum\ModuleStatus;
use AppBundle\Entity\Event;
use AppBundle\Entity\Module;
use AppBundle\Entity\module\PollModule;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
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
        if ($type == 'pollmodule') { // TODO define constantes
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
    public function treatUpdateFormeModule(Form $moduleForm)
    {
        $this->module = $moduleForm->getData();

        // TODO Check ?

        $this->entityManager->persist($this->module);
        $this->entityManager->flush();
        return $this->module;
    }

    /**
     * @param Module $module Le module à afficher
     * @param $allowEdit boolean "true" si le module est éditable
     * @param Request $request
     * @return string La vue HTML sous forme de string
     */
    public function displayModulePartial(Module $module, $allowEdit, Form $moduleForm, Request $request)
    {
        if ($module->getPollModule() != null) {
            return $this->templating->render("@App/Event/module/displayPollModule.html.twig", array(
                "module" => $module,
                "allowEdit" => $allowEdit,
                'moduleForm' => $moduleForm->createView()
            ));
        } elseif ($module->getExpenseModule() != null) {
            return $this->templating->render("@App/Event/module/displayExpenseModule.html.twig", [
                "module" => $module,
                "allowEdit" => $allowEdit,
                'moduleForm' => $moduleForm->createView()
            ]);
        } else {
            return $this->templating->render("@App/Event/module/displayModule.html.twig", [
                "module" => $module,
                "allowEdit" => $allowEdit,
                'moduleForm' => $moduleForm->createView()
            ]);
        }
    }
}