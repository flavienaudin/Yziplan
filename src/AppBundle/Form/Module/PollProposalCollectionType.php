<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/07/2016
 * Time: 10:15
 */

namespace AppBundle\Form\Module;

use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Module\PollProposal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class PollProposalCollectionType extends AbstractType
{
    private $pollModule;
    private $pollModuleInvitation;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
            /** @var FormInterface $form */
            $form = $formEvent->getForm();
            $data = $formEvent->getData();
            if (key_exists('pollModule', $data)) {
                $this->setPollModule($data['pollModule']);
            }
            if (key_exists('moduleInvitation', $data)) {
                $this->setPollModuleInvitation($data['moduleInvitation']);
            }

            $form->add("pollProposals", CollectionType::class, array(
                'entry_type' => PollProposalType::class,
                'required' => false,
                'mapped' => false,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_data' => $this->getPollProposalElementForPrototype()
            ));
        });
    }

    /**
     * @return PollProposal
     */
    private function getPollProposalElementForPrototype(){
        if ($this->pollModule != null && $this->pollModuleInvitation !=null){
            $pollProposal = new PollProposal();
            $pollProposal->setCreator($this->pollModuleInvitation);
            $pollProposal->initializeWithPollModule($this->pollModule);
            return $pollProposal;
        }
        return null;
    }

    /**
     * @return PollModule
     */
    public function getPollModule()
    {
        return $this->pollModule;
    }

    /**
     * @param PollModule $pollModule
     */
    public function setPollModule($pollModule)
    {
        $this->pollModule = $pollModule;
    }

    /**
     * @return ModuleInvitation
     */
    public function getPollModuleInvitation()
    {
        return $this->pollModuleInvitation;
    }

    /**
     * @param ModuleInvitation $pollModuleInvitation
     */
    public function setPollModuleInvitation($pollModuleInvitation)
    {
        $this->pollModuleInvitation = $pollModuleInvitation;
    }



}