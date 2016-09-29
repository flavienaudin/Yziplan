<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/09/2016
 * Time: 15:21
 */

namespace AppBundle\Form;


use AppBundle\Utils\enum\EventInvitationAnswer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\Translator;

class InvitationAnswerFormType extends AbstractType
{
    /** @var Translator $translator */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'required' => true,
            'expanded' => true,
            'choices' => array(
                EventInvitationAnswer::DONT_KNOW,
                EventInvitationAnswer::YES,
                EventInvitationAnswer::INTERESTED,
                EventInvitationAnswer::NO
            ),
            'prefered_choice' => EventInvitationAnswer::DONT_KNOW,
            "choice_label" => function ($value, $key, $idx) {
                return $this->translator->trans($value);
            }
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

}