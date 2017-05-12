<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 20/06/2016
 * Time: 15:20
 */

namespace AppBundle\Form\Module;


use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Form\Module\PollModule\PollModuleFormType;
use AppBundle\Form\Type\InvitationRuleType;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\InvitationRule;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class ModuleType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * ModuleType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class, array(
                'required' => false
            ))
            ->add("description", TextareaType::class, array(
                'required' => false
            ))
            ->add('invitationRule', InvitationRuleType::class, array(
                'required' => true,
                'empty_data' => InvitationRule::EVERYONE
            ));
        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
                /** @var FormInterface $form */
                $form = $formEvent->getForm();
                /** @var Module $module */
                $module = $formEvent->getData();

                /*---   GESTION DES INVITATIONS AU MODULE   ---*/
                $choices = array();
                $selection = array();
                /** @var ModuleInvitation $moduleInvitation */
                foreach ($module->getModuleInvitations() as $moduleInvitation) {
                    if ($moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::VALID
                        || $moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::AWAITING_ANSWER
                        || ($moduleInvitation->isOrganizer() && $moduleInvitation->getEventInvitation()->getStatus() != EventInvitationStatus::CANCELLED)
                    ) {
                        $choices[] = $moduleInvitation;
                        if ($moduleInvitation->getStatus() == ModuleInvitationStatus::INVITED) {
                            $selection[] = $moduleInvitation;
                        }
                    }
                }
                $form->add("moduleInvitationSelected", ChoiceType::class, array(
                    'mapped' => false,
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $choices,
                    'data' => $selection,
                    'choice_value' => function ($moduleInvitation) {
                        /** @var ModuleInvitation $moduleInvitation */
                        return $moduleInvitation->getToken();
                    },
                    'choice_label' => function ($moduleInvitation, $key, $index) {
                        /** @var ModuleInvitation $moduleInvitation */
                        $label = $moduleInvitation->getDisplayableName(true, false);
                        if ($moduleInvitation->isOrganizer()) {
                            $label .= '(' . $this->translator->trans('module.form.moduleInvitationSelected.creator') . ')';
                        }
                        return $label;
                    },
                    'choice_attr' => function ($moduleInvitation, $key, $index) {
                        /** @var ModuleInvitation $moduleInvitation */
                        $attr = array();
                        if ($moduleInvitation->isCreator()) {
                            $attr['disabled'] = 'disabled';
                        }
                        return $attr;
                    },
                    'group_by' => function ($moduleInvitation, $key, $index) {
                        /** @var ModuleInvitation $moduleInvitation */
                        return $moduleInvitation->getStatus();
                    }
                ));

                /*---   GESTION SPECIFIQUE AU POLL MODULE   ---*/
                if ($module->getPollModule() != null) {
                    $form->add('pollModule', PollModuleFormType::class, array(
                        'required' => false
                    ));
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Module::class
        ));
    }
}