<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 21/04/2017
 * Time: 10:47
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\KittyModule;
use AppBundle\Entity\Module\KittyParticipation;
use AppBundle\Form\Module\KittyModule\KittyParticipationType;
use AppBundle\Utils\enum\KittyParticipationStatus;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Templating\EngineInterface;

class KittyModuleManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $templating;

    /** @var KittyModule */
    private $kittyModule;

    /** @var KittyParticipation */
    private $kittyParticipation;

    /**
     * KittyModuleManager constructor.
     * @param EntityManager $doctrine
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface $templating
     */
    public function __construct(EntityManager $doctrine, FormFactoryInterface $formFactory, EngineInterface $templating)
    {
        $this->entityManager = $doctrine;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
    }

    /**
     * Creation du formulaire de participation à une cagnotte
     * @return FormInterface
     */
    public function createParticipationForm(KittyModule $kittyModule)
    {
        return $this->formFactory->createNamed('kitty-' . $kittyModule->getModule()->getToken() . '-participationForm', KittyParticipationType::class);
    }

    /**
     * @param FormInterface $participationForm
     * @param KittyModule $kittyModule
     * @param ModuleInvitation|null $moduleInvitation
     * @return KittyParticipation|mixed
     */
    public function treatParticipationForm(FormInterface $participationForm, KittyModule $kittyModule, ModuleInvitation $moduleInvitation)
    {
        $this->kittyParticipation = $participationForm->getData();
        if ($this->kittyParticipation->getPayer() == null) {
            $this->kittyParticipation->setPayer($moduleInvitation);
        }
        if ($this->kittyParticipation->getKittyModule() == null) {
            $this->kittyModule = $kittyModule;
            $this->kittyModule->addKittyParticipation($this->kittyParticipation);
        }

        // TODO Gérer le paiement et le status qui va avec le retour de MangoPay
        $this->kittyParticipation->setStatus(KittyParticipationStatus::OK);

        $this->kittyModule->updateTotalAmount();

        $this->entityManager->persist($this->kittyParticipation);
        $this->entityManager->flush();

        // TODO : gerer les notifications
        //$this->notificationManager->createAddPollProposalNotifications($this->pollProposal, ($moduleInvitation != null ? $moduleInvitation->getEventInvitation() : null));
        return $this->kittyParticipation;
    }
}