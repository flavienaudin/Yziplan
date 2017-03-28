<?php
/**
 * User: Patrick
 * Date: 27/03/2017
 * Time: 15:03
 */

namespace AppBundle\Form\Module\PollModule;


use AppBundle\Entity\Module\PollProposal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Utiliser pour le module QUoi et qui apporte quoi
 *
 * lass PollProposalWhatType
 * @package AppBundle\Form\Module
 */
class PollProposalWhatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
            /** @var FormInterface $form */
            $form = $formEvent->getForm();
            /** @var PollProposal $pollProposal */
            $pollProposal = $formEvent->getData();

            $form
                ->add('valString', TextType::class, array(
                    'required' => true,
                    'constraints' => new NotBlank()
                ));

            if (($pollProposal != null) && !empty($pollProposal->getId())) {
                $form->add('id', HiddenType::class, array(
                    'disabled' => true
                ));
            }

        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PollProposal::class
        ));
    }
}