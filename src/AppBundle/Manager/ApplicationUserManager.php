<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 12/09/2016
 * Time: 15:39
 */

namespace AppBundle\Manager;

use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\User\ApplicationUser;
use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Utils\enum\AppUserStatus;
use ATUserBundle\Entity\AccountUser;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class ApplicationUserManager
{

    /** @var EntityManager $entityManager */
    private $entityManager;
    /** @var CanonicalizerInterface $emailCanonicalizer */
    private $emailCanonicalizer;
    /** @var Translator $translator */
    private $translator;
    /** @var ApplicationUser */
    private $applicationUser;
    /** @var TokenGeneratorInterface $tokenGenerator */
    private $tokenGenerator;

    /**
     * ApplicationUserManager constructor.
     * @param EntityManager $entityManager
     * @param CanonicalizerInterface $emailCanonicalizer
     */
    public function __construct(EntityManager $entityManager, CanonicalizerInterface $emailCanonicalizer, Translator $translator, TokenGeneratorInterface $tokenGenerator)
    {
        $this->entityManager = $entityManager;
        $this->emailCanonicalizer = $emailCanonicalizer;
        $this->translator = $translator;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @return ApplicationUser
     */
    public function getApplicationUser()
    {
        return $this->applicationUser;
    }

    /**
     * @param ApplicationUser $applicationUser
     */
    public function setApplicationUser(ApplicationUser $applicationUser)
    {
        $this->applicationUser = $applicationUser;
    }

    /**
     * Retrieve the ApplicationUser by it AppUserEmail
     * @param $email
     * @return ApplicationUser|null
     */
    public function retrieveApplicationUserByEmail($email)
    {
        $this->applicationUser = null;
        /** @var AppUserEmail $appUserEmail */
        $appUserEmail = $this->findAppUserEmailByEmail($email);
        if ($appUserEmail != null) {
            $this->applicationUser = $appUserEmail->getApplicationUser();
        }
        return $this->applicationUser;
    }

    /**
     * @param AccountUser $user
     * @return Collection|null
     */
    public function getUserEventInvitations(AccountUser $user)
    {
        return $user->getApplicationUser()->getEventInvitations();
    }

    /**
     * @param $email string Email to look for
     * @return AppUserEmail|null
     */
    public function findAppUserEmailByEmail($email)
    {
        return $this->entityManager->getRepository(AppUserEmail::class)->findOneBy(['emailCanonical' => $this->emailCanonicalizer->canonicalize($email)]);
    }

    /**
     * @param $confirmationToken string confirmationToken to look for
     * @return AppUserEmail|null
     */
    public function findAppUserEmailByConfirmationToken($confirmationToken)
    {
        return $this->entityManager->getRepository(AppUserEmail::class)->findOneBy(['confirmationToken' => $confirmationToken]);
    }

    /**
     * Create an ApplicationUser with AppUserEmail from the given email. No AccountUser is created.
     *
     * /!\ Email should be used by another AppUserEmail.
     *
     * @param $email string
     * @return ApplicationUser
     */
    public function createApplicationUserFromEmail($email)
    {
        /** @var ApplicationUser $applicationUser */
        $this->applicationUser = new ApplicationUser();
        $appUserEmail = new AppUserEmail();
        $appUserEmail->setEmail($email);
        $appUserEmail->setEmailCanonical($this->emailCanonicalizer->canonicalize($appUserEmail->getEmail()));
        $appUserEmail->setUseToReceiveEmail(false);
        $this->applicationUser->addAppUserEmail($appUserEmail);
        $this->entityManager->persist($this->applicationUser);
        $this->entityManager->flush();
        return $this->applicationUser;
    }

    /**
     * @param FormInterface $appUserEmailForm
     * @return FormInterface|AppUserEmail
     */
    public function treatAddAppUserEmailForm(FormInterface $appUserEmailForm)
    {
        if ($this->applicationUser != null) {
            /** @var AppUserEmail $newAppUserEmail */
            $newAppUserEmail = $appUserEmailForm->getData();
            $existingAppUserEmail = $this->findAppUserEmailByEmail($newAppUserEmail->getEmail());
            if ($existingAppUserEmail != null){
                if($existingAppUserEmail->getApplicationUser()->getAccountUser() != null) {
                    // L'email est déjà attatché à un AccountUser, il ne peut être utilisé
                    $appUserEmailForm->get('email')->addError(new FormError($this->translator->trans("profile.show.appuseremail.modal.form.validation.email_already_used")));
                    return $appUserEmailForm;
                }else{
                    // l'email existe mais n'est pas rattaché à un AccountUser, il pourra être rattaché à l'utilisateur uniquement après validation par email
                    // pour ne pas bloquer les éventuelles invitations qui seraient rattachés à son ApplicationUser
                    $existingAppUserEmail->setType($newAppUserEmail->getType());
                    $existingAppUserEmail->setUseToReceiveEmail($newAppUserEmail->isUseToReceiveEmail());
                    $this->entityManager->persist($this->applicationUser);
                    $this->entityManager->flush();
                    return $existingAppUserEmail;
                }
            }else {
                // L'email n'est pas connu en base, on l'attache à l'utilisateur directement (peut être soumis à validation par email)
                $newAppUserEmail->setEmailCanonical($this->emailCanonicalizer->canonicalize($newAppUserEmail->getEmail()));
                $this->applicationUser->addAppUserEmail($newAppUserEmail);
                $this->entityManager->persist($this->applicationUser);
                $this->entityManager->flush();
                return $appUserEmailForm;
            }
        }
        $appUserEmailForm->addError(new FormError($this->translator->trans("profile.message.update.error")));
        return $appUserEmailForm;
    }

    /**
     * Fusionne deux ApplicationUser en adaptant les status
     * Les ApplicationUsers ne sont pas persistées
     * @param ApplicationUser $mainApplicationUser
     * @param ApplicationUser $mergedApplicationUser
     */
    public function mergeApplicationUsers(ApplicationUser $mainApplicationUser, ApplicationUser $mergedApplicationUser)
    {
        $mainApplicationUser->setStatus(AppUserStatus::MAIN);
        $mainApplicationUser->addMergedAppUser($mergedApplicationUser);
        /** @var EventInvitation $eventInvitation */
        foreach ($mergedApplicationUser->getEventInvitations() as $eventInvitation) {
            $mergedApplicationUser->removeEventInvitation($eventInvitation);
            $mainApplicationUser->addEventInvitation($eventInvitation);
        }
    }
}