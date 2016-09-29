<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 24/05/2016
 * Time: 18:57
 */

namespace ATUserBundle\Security;

use AppBundle\Manager\GenerateursToken;
use ATUserBundle\Entity\AccountUser;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;


class ATUserProvider extends FOSUBUserProvider
{
    /** @var GenerateursToken */
    protected $tokenGenerateur;

    /**
     * @param GenerateursToken $tokenGenerateur
     */
    public function setTokenGenerateur(GenerateursToken $tokenGenerateur)
    {
        $this->tokenGenerateur = $tokenGenerateur;
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
                }
            }

            if ($response instanceof PathUserResponse && $user instanceof AccountUser) {
                $user->getApplicationUser()->getAppUserInformation()->setPublicName($response->getNickname());
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