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
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ApplicationUserManager
{

    /** @var EntityManager $entityManager */
    private $entityManager;
    /** @var CanonicalizerInterface $emailCanonicalizer */
    private $emailCanonicalizer;
    /** @var TranslatorInterface $translator */
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
    public function __construct(EntityManager $entityManager, CanonicalizerInterface $emailCanonicalizer, TranslatorInterface $translator, TokenGeneratorInterface $tokenGenerator)
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
     * @return array
     */
    public function getUserEventInvitations(AccountUser $user, $eventFilter)
    {
        $eventInvitationRepo = $this->entityManager->getRepository(EventInvitation::class);
        if($eventFilter == '#upcomingEvents') {
            return $eventInvitationRepo->getUpcomingEventInvitationsByApplicationUser($user->getApplicationUser());
        }else{
            return $eventInvitationRepo->getPassedArchivedEventInvitationsByApplicationUser($user->getApplicationUser());
        }
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
            if ($existingAppUserEmail != null) {
                if ($existingAppUserEmail->getApplicationUser()->getAccountUser() != null) {
                    // L'email est d??j?? attatch?? ?? un AccountUser, il ne peut ??tre utilis??
                    $appUserEmailForm->get('email')->addError(new FormError($this->translator->trans("profile.show.appuseremail.modal.form.validation.email_already_used")));
                    return $appUserEmailForm;
                } else {
                    // l'email existe mais n'est pas rattach?? ?? un AccountUser, il pourra ??tre rattach?? ?? l'utilisateur uniquement apr??s validation par email
                    // pour ne pas bloquer les ??ventuelles invitations qui seraient rattach??s ?? son ApplicationUser
                    $existingAppUserEmail->setType($newAppUserEmail->getType());
                    $existingAppUserEmail->setUseToReceiveEmail($newAppUserEmail->isUseToReceiveEmail());
                    $this->entityManager->persist($this->applicationUser);
                    $this->entityManager->flush();
                    return $existingAppUserEmail;
                }
            } else {
                // L'email n'est pas connu en base, on l'attache ?? l'utilisateur directement (peut ??tre soumis ?? validation par email)
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
     * Les ApplicationUsers ne sont pas persist??es
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