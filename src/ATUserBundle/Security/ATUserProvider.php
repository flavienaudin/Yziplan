<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 24/05/2016
 * Time: 18:57
 */

namespace ATUserBundle\Security;

use AppBundle\Entity\User\ApplicationUser;
use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Event\AppUserEmailEvent;
use AppBundle\Manager\GenerateursToken;
use ATUserBundle\ATUserEvents;
use ATUserBundle\Entity\AccountUser;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class ATUserProvider extends FOSUBUserProvider
{
    /** @var GenerateursToken $tokenGenerateur */
    protected $tokenGenerateur;
    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /** @param GenerateursToken $tokenGenerateur */
    public function setTokenGenerateur(GenerateursToken $tokenGenerateur)
    {
        $this->tokenGenerateur = $tokenGenerateur;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();
        //on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($service);
        $setter_id = $setter . 'Id';
        $setter_token = $setter . 'AccessToken';
        //we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }
        //we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));

        //when the user is registrating
        if (null === $user) {
            $user = $this->userManager->findUserByEmail($response->getEmail());
            $service = $response->getResourceOwner()->getName();
            $setter = 'set' . ucfirst($service);
            $setter_id = $setter . 'Id';
            $setter_token = $setter . 'AccessToken';

            if ($user == null) {
                // create new user here
                $user = $this->userManager->createUser();
                if ($response instanceof PathUserResponse) {
                    $user->setUsername($response->getEmail());
                    $user->setEmail($response->getEmail());
                }
                $user->setPlainPassword($this->tokenGenerateur->random(GenerateursToken::MOTDEPASSE_LONGUEUR));
                if ($user instanceof AccountUser) {
                    $user->setPasswordKnown(false);

                    /** @var AppUserEmail $appUserEmail */
                    $appUserEmail = $this->userManager->findAppUserEmailByEmail($response->getEmail());
                    if ($appUserEmail == null) {
                        $appUserEmail = new AppUserEmail();
                        $appUserEmail->setEmail($response->getEmail());
                        $appUserEmail->setUseToReceiveEmail(true);
                        $applicationUser = new ApplicationUser();
                        $applicationUser->addAppUserEmail($appUserEmail);
                        $user->setApplicationUser($applicationUser);
                        $applicationUser->setAccountUser($user);
                    } elseif ($appUserEmail->getApplicationUser()->getAccountUser() == null) {
                        $appUserEmail->setUseToReceiveEmail(true);
                        $appUserEmail->setApplicationUser($user->getApplicationUser());
                        $user->setApplicationUser($appUserEmail->getApplicationUser());
                    } elseif ($appUserEmail->getApplicationUser()->getAccountUser()->getEmail() != $response->getEmail()) {
                        // L'email de la nouvelle inscription OAuth est déjà rattaché à un autre compte => envoi d'email d'avertissement.
                        $userEmailEvent = new AppUserEmailEvent($appUserEmail);
                        $this->eventDispatcher->dispatch(ATUserEvents::OAUTH_REGISTRATION_SUCCESS, $userEmailEvent);

                        $appUserEmail->setType(null);
                        $appUserEmail->setUseToReceiveEmail(true);
                        $user->getApplicationUser()->addAppUserEmail($appUserEmail);
                    }
                }
            }
            if ($response instanceof PathUserResponse && $user instanceof AccountUser) {
                // On récupère ce qu'on peut du profil.
                // On verifie que le PublicName n'est pas sull, car il est possible de se connecter via OAuth après avoir créer un compte,
                // donc il ne faut pas écraser les informations.
                if($user->getApplicationUser()->getAppUserInformation()->getPublicName() == null){
                    $user->getApplicationUser()->getAppUserInformation()->setPublicName($response->getNickname());
                }
                if($response->getProfilePicture()!=null && $user->getApplicationUser()->getAppUserInformation()->getAvatar() == null){
                    $user->getApplicationUser()->getAppUserInformation()->setAvatar($response->getProfilePicture());
                }
                if($response->getFirstName()!=null && $user->getApplicationUser()->getAppUserInformation()->getFirstName() == null){
                    $user->getApplicationUser()->getAppUserInformation()->setFirstName($response->getFirstName());
                }
                if($response->getLastName()!=null && $user->getApplicationUser()->getAppUserInformation()->getLastName() == null){
                    $user->getApplicationUser()->getAppUserInformation()->setLastName($response->getLastName());
                }
                if($response->getUsername()!=null && $user->getApplicationUser()->getAppUserInformation()->getLastName() == null){
                    $user->getApplicationUser()->getAppUserInformation()->setLastName($response->getLastName());
                }
            }

            $user->setEnabled(true);
            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());

            $this->userManager->updateUser($user);
            return $user;
        }
        //if user exists - go with the HWIOAuth way
        $user = parent::loadUserByOAuthUserResponse($response);
        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';
        //update access token
        $user->$setter($response->getAccessToken());
        return $user;
    }
}