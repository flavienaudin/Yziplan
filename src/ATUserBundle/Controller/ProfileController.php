<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/06/2016
 * Time: 11:23
 */

namespace ATUserBundle\Controller;

use AppBundle\Utils\FlashBagTypes;
use AppBundle\Utils\FormUtils;
use ATUserBundle\Entity\User;
use ATUserBundle\Form\UserAboutBasicInformationType;
use ATUserBundle\Form\UserAboutBiographyType;
use ATUserBundle\Manager\UserAboutManager;
use ATUserBundle\Manager\UserManager;
use FOS\UserBundle\Controller\ProfileController as BaseController;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        if (!is_object($user) || !$user instanceof User) {
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
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.profile.form.factory');
        $userForm = $formFactory->createForm();
        $userForm->setData($user);

        /** User About */
        $userAbout = $this->get("at.manager.user_about_manager")->getUserAbout($user);
        $biographyForm = $this->createForm(UserAboutBiographyType::class, $userAbout);
        $basicInformationForm = $this->createForm(UserAboutBasicInformationType::class, $userAbout);

        return $this->render('FOSUserBundle:Profile:show.html.twig', array(
            'user' => $user,
            'form_mandatory_information' => $userForm->createView(),
            'form_biography' => $biographyForm->createView(),
            'form_basic_information' => $basicInformationForm->createView()
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
        if ($user instanceof User) {
            /** @var UserManager $userManager */
            $userManager = $this->get("at.manager.user_manager");
            /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
            $dispatcher = $this->get('event_dispatcher');

            /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
            $formFactory = $this->get('fos_user.profile.form.factory');
            $userForm = $formFactory->createForm();
            $userForm->setData($user);

            $userForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($userForm->isValid()) {
                    $event = new FormEvent($userForm, $request);
                    $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

                    $userManager->updateUser($user);

                    // TODO necessaire ?
                    //$data['messages'][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans("profile.message.update.success");
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
                return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
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
        if ($user instanceof User) {
            /** @var UserAboutManager $userAboutManager */
            $userAboutManager = $this->get("at.manager.user_about_manager");
            $userAbout = $userAboutManager->getUserAbout($user);
            $biographyForm = $this->createForm(UserAboutBiographyType::class, $userAbout);

            $biographyForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($biographyForm->isValid()) {
                    $userAboutManager->updateUserAbout($userAbout);

                    // TODO necessaire ?
                    //$data['messages'][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans("profile.message.update.success");
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
                return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
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
        if ($user instanceof User) {
            /** @var UserAboutManager $userAboutManager */
            $userAboutManager = $this->get("at.manager.user_about_manager");
            $userAbout = $userAboutManager->getUserAbout($user);
            $basicInformationForm = $this->createForm(UserAboutBasicInformationType::class, $userAbout);

            $basicInformationForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($basicInformationForm->isValid()) {
                    $userAboutManager->updateUserAbout($userAbout);

                    // TODO necessaire ?
                    //$data["message"] = $this->get("translator")->trans("profile.message.update.success");
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
            } else if($basicInformationForm->isValid()) {
                $userAboutManager->updateUserAbout($userAbout);
                $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator")->trans("profile.message.update.success"));
                return $this->redirectToRoute("fos_user_profile_show");
            }else{
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("profile.message.update.error"));
                return $this->redirectToRoute("fos_user_profile_show");
            }
        }else{
            if ($request->isXmlHttpRequest()) {
                $data[FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("profile.message.update.error");
                return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("profile.message.update.error"));
                return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
            }
        }
    }
}