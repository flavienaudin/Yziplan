<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 04/05/2017
 * Time: 15:02
 */

namespace AppBundle\Form\Module\PollModule;


use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Form\Type\InvitationRuleType;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\InvitationRule;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ModuleInvitationsType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * ModuleInvitationsType constructor.
     * @param TranslatorInterface $translator
     * @param EngineInterface $templating
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                $choices = array();
                $selection = array();
                /** @var ModuleInvitation $moduleInvitation */
                foreach ($module->getModuleInvitations() as $moduleInvitation) {
                    if ($moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::VALID
                        || $moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::AWAITING_ANSWER
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
            });
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Module::class
        ));
    }
}