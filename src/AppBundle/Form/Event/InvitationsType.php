<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 06/09/2016
 * Time: 11:10
 */

namespace AppBundle\Form\Event;


use AppBundle\Entity\User\Contact;
use ATUserBundle\Entity\AccountUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InvitationsType extends AbstractType
{

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var  ValidatorInterface */
    private $validator;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $tokenStorage, ValidatorInterface $validator)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
                /** @var FormInterface $form */
                $form = $formEvent->getForm();

                /*$userContactsUser = array();
                if ($this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)) {
                    $user = $this->tokenStorage->getToken()->getUser();
                    if ($user instanceof AccountUser) {
                        $userContactsUser = $user->getApplicationUser()->getValidContacts();
                    }
                }*/

                $form
                    ->add('invitations', ChoiceType::class, array(
                        'mapped' => false,
                        'required' => false,
                        'multiple' => true/*,
                        'choices' => $userContactsUser,
                        'choice_label' => 'displayableName',
                        'choice_value' => 'emailToContact'*/
                    ));
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $formEvent) {
                $data = $formEvent->getData();
                $emails = array();
                if (isset($data['invitations']) && is_array($data['invitations'])) {
                    $emailConstraint = new Email();
                    foreach ($data['invitations'] as $email) {
                        $errors = $this->validator->validate($email, $emailConstraint);
                        if (count($errors) == 0) {
                            $emails[$email] = $email;
                        }
                    }
                }
                $formEvent->getForm()->add('invitations', ChoiceType::class, [
                    'mapped' => false,
                    'required' => false,
                    'multiple' => true,
                    'choices' => $emails,
                    'invalid_message' => "invitations.form.invitations.invalid_message"
                ]);
            });
    }
}