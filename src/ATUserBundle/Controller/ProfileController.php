<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/06/2016
 * Time: 11:23
 */

namespace ATUserBundle\Controller;

use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\FormUtils;
use ATUserBundle\Entity\AccountUser;
use ATUserBundle\Form\UserAboutBiographyType;
use ATUserBundle\Manager\UserManager;
use FOS\UserBundle\Controller\ProfileController as BaseController;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;


class ProfileController extends BaseController
{
    /**
     * Show the user
     */
    public function showAction(Request $request = null)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof AccountUser) {
            throw $this->createAccessDeniedException('This user does not have access to this section.');
        }
        /** Profile edition */
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** User Profile */
        $userForm = $this->get("at.manager.user")->createProfileForm($user);

        /** User About */
        $appUserInformationManager = $this->get("at.manager.app_user_information");
        $appUserInformationManager->retrieveAppUserInformation($user);
        $biographyForm = $appUserInformationManager->createBiographyForm();
        $basicInformationForm = $appUserInformationManager->createBasicInformationForm();

        return $this->render('FOSUserBundle:Profile:show.html.twig', array(
            'user' => $user,
            'form_mandatory_information' => $userForm->createView(),
            'form_biography' => ($biographyForm != null ? $biographyForm->createView() : null),
            'form_basic_information' => ($basicInformationForm != null ? $basicInformationForm->createView() : $basicInformationForm)
        ));
    }

    /**
     * Edit the user
     */
    public function editAction(Request $request)
    {
        return $this->redirectToRoute("fos_user_profile_show");
    }

    /**
     * @Route("/update-user-profile", name="updateUserProfile")
     */
    public function updateUserProfileAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $user = $this->getUser();
        $data = array();
        if ($user instanceof AccountUser) {
            /** @var UserManager $userManager */
            $userManager = $this->get("at.manager.user");
            /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
            $dispatcher = $this->get('event_dispatcher');

            $userForm = $userManager->createProfileForm($user);
            $userForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($userForm->isValid()) {
                    $event = new FormEvent($userForm, $request);
                    $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

                    $userManager->updateUser($user);

                    $data['data']['username'] = $user->getUsername();
                    $data['data']['email'] = $user->getEmail();
                    $data['data']['pseudo'] = $user->getPseudo();

                    $response = new JsonResponse($data, Response::HTTP_OK);
                    $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                    if ($request->hasSession()) {
                        // Pas de flashbag message sinon ils seraient affichés sur une page ultérieurement
                        $this->get('session')->getFlashBag()->clear();
                    }
                    return $response;
                } else {
                    $data["formErrors"] = array();
                    foreach ($userForm->getErrors(true) as $error) {
                        $data["formErrors"][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                    }
                    return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            } else if ($userForm->isValid()) {
                $event = new FormEvent($userForm, $request);
                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);
                $userManager->updateUser($user);
                return $this->redirectToRoute("fos_user_profile_show");
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("profile.message.update.error"));
                return $this->redirectToRoute("fos_user_profile_show");
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                $data[FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("profile.message.update.error");
                return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("profile.message.update.error"));
                return $this->redirectToRoute('fos_user_profile_show');
            }
        }
    }

    /**
     * @Route("/update-user-biography", name="updateUserBiography")
     */
    public function updateUserBiographyAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $user = $this->getUser();
        $data = array();
        if ($user instanceof AccountUser) {
            /** @var AppUSerInformationManager $userAboutManager */
            $userAboutManager = $this->get("at.manager.user_about");
            $userAbout = $userAboutManager->retrieveUserAbout($user);
            $biographyForm = $this->createForm(UserAboutBiographyType::class, $userAbout);

            $biographyForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($biographyForm->isValid()) {
                    $userAboutManager->updateUserAbout($userAbout);
                    $data["data"]["biography"] = nl2br(empty($userAbout->getBiography()) ? $this->get("translator")->trans("profile.show.about.biography.empty") : $userAbout->getBiography());
                    return new JsonResponse($data, Response::HTTP_OK);
                } else {
                    $data["formErrors"] = array();
                    foreach ($biographyForm->getErrors(true) as $error) {
                        $data["formErrors"][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                    }
                    return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            } else if ($biographyForm->isValid()) {
                $userAboutManager->updateUserAbout($userAbout);
                $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator")->trans("profile.message.update.success"));
                return $this->redirectToRoute("fos_user_profile_show");
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("profile.message.update.error"));
                return $this->redirectToRoute("fos_user_profile_show");
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                $data[FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("profile.message.update.error");
                return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("profile.message.update.error"));
                return $this->redirectToRoute("fos_user_profile_show");
            }
        }
    }

    /**
     * @Route("/update-user-basic-information", name="updateUserBasicInformation")
     */
    public function updateUserBasicInformationAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $user = $this->getUser();
        $data = array();
        if ($user instanceof AccountUser) {
            $userAboutManager = $this->get("at.manager.user_about");
            $userAbout = $userAboutManager->retrieveUserAbout($user);
            $basicInformationForm = $userAboutManager->createBasicInformationForm();
            $basicInformationForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($basicInformationForm->isValid()) {
                    $userAboutManager->updateUserAbout($userAbout);
                    // TODO : Gérer le format de la date en fonction de la locale
                    $data["data"] = array(
                        'fullname' => (!empty($userAbout->getFullname()) ? $userAbout->getFullname() : '-'),
                        'gender' => ($userAbout->getGender() != null ? $this->get('translator')->trans("global.gender." . $userAbout->getGender()) : '-'),
                        'birthday' => ($userAbout->getBirthday() != null ? $userAbout->getBirthday()->format("d/m/Y") : '-')
                    );
                    return new JsonResponse($data, Response::HTTP_OK);
                } else {
                    $data["formErrors"] = array();
                    foreach ($basicInformationForm->getErrors(true) as $error) {
                        $data["formErrors"][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                    }
                    return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            } else if ($basicInformationForm->isValid()) {
                $userAboutManager->updateUserAbout($userAbout);
                $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator")->trans("profile.message.update.success"));
                return $this->redirectToRoute("fos_user_profile_show");
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("profile.message.update.error"));
                return $this->redirectToRoute("fos_user_profile_show");
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                $data[FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("profile.message.update.error");
                return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("profile.message.update.error"));
                return $this->redirectToRoute("fos_user_profile_show");
            }
        }
    }
}