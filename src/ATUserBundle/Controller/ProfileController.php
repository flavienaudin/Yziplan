<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/06/2016
 * Time: 11:23
 */

namespace ATUserBundle\Controller;

use AppBundle\Utils\FlashBagTypes;
use ATUserBundle\Entity\User;
use ATUserBundle\Form\UserAboutBasicInformationType;
use ATUserBundle\Form\UserAboutBiographyType;
use ATUserBundle\Manager\UtilisateurManager;
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
            throw new AccessDeniedException('This user does not have access to this section.');
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
     * @Route("/update-user-profile", name="updateUserProfileAjax")
     */
    public function updateUserProfileAjaxAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);

            $data = array();
            $reponseStatus = 400;

            $user = $this->getUser();
            if ($user instanceof User) {
                /** @var UtilisateurManager $userManager */
                $userManager = $this->get("at.manager.user_manager");
                /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
                $dispatcher = $this->get('event_dispatcher');


                /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
                $formFactory = $this->get('fos_user.profile.form.factory');
                $userForm = $formFactory->createForm();
                $userForm->setData($user);

                $userForm->handleRequest($request);

                if ($userForm->isValid()) {
                    $event = new FormEvent($userForm, $request);
                    $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

                    $userManager->updateUser($user);

                    $reponseStatus = 200;
                    $data["error"] = false;
                    $data['data']['username'] = $user->getUsername();
                    $data['data']['email'] = $user->getEmail();
                    $data['data']['pseudo'] = $user->getPseudo();

                    $response = new JsonResponse($data, $reponseStatus);

                    $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                    if($request->hasSession()){
                        $this->get('session')->getFlashBag()->clear();
                    }

                    return $response;
                } else {
                    $data["error"] = array();
                    foreach ($userForm->getErrors(true) as $error) {
                        $data["error"][$error->getOrigin()->getName()] = $error->getMessage();
                    }
                }
            } else {
                $data["error"] = true;
                $data["message"] = $this->get("translator")->trans("profile.message.update.error");
            }
            return new JsonResponse($data, $reponseStatus);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("global.error.not_ajax_request"));
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/update-user-biography", name="updateUserBiographyAjax")
     */
    public function updateUserBiographyAjaxAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);

            $data = array();
            $reponseStatus = 400;

            $user = $this->getUser();
            if ($user instanceof User) {
                $userAboutManager = $this->get("at.manager.user_about_manager");
                $userAbout = $userAboutManager->getUserAbout($user);
                $biographyForm = $this->createForm(UserAboutBiographyType::class, $userAbout);
                $biographyForm->handleRequest($request);
                if ($biographyForm->isValid()) {
                    $userAboutManager->updateUserAbout($userAbout);

                    $reponseStatus = 200;
                    $data["error"] = false;
                    $data["message"] = $this->get("translator")->trans("profile.message.update.success");
                    $data["data"]["biography"] = nl2br($userAbout->getBiography());
                } else {
                    $data["error"] = array();
                    foreach ($biographyForm->getErrors(true) as $error) {
                        $data["error"][$error->getOrigin()->getName()] = $error->getMessage();
                    }
                }
            } else {
                $data["error"] = true;
                $data["message"] = $this->get("translator")->trans("profile.message.update.error");
            }
            return new JsonResponse($data, $reponseStatus);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("global.error.not_ajax_request"));
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/update-user-basic-information", name="updateUserBasicInformationAjax")
     */
    public function updateUserBasicInformationAjaxAction(Request $request)
    {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);

            $data = array();
            $reponseStatus = 400;

            $user = $this->getUser();
            if ($user instanceof User) {
                $userAboutManager = $this->get("at.manager.user_about_manager");
                $userAbout = $userAboutManager->getUserAbout($user);
                $basicInformationForm = $this->createForm(UserAboutBasicInformationType::class, $userAbout);
                $basicInformationForm->handleRequest($request);
                if ($basicInformationForm->isValid()) {
                    $userAboutManager->updateUserAbout($userAbout);

                    $reponseStatus = 200;
                    $data["message"] = $this->get("translator")->trans("profile.message.update.success");
                    // TODO : Gérer le format de la date en fonction de la locale
                    $data["data"] = array(
                        'fullname' => (!empty($userAbout->getFullname())?$userAbout->getFullname():'-'),
                        'gender' => ($userAbout->getGender() != null ? $this->get('translator')->trans("global.gender." . $userAbout->getGender()) : '-'),
                        'birthday' => ($userAbout->getBirthday() != null ? $userAbout->getBirthday()->format("d/m/Y") : '-')
                    );
                } else {
                    $data["error"] = array();
                    foreach ($basicInformationForm->getErrors(true) as $error) {
                        $data["error"][$error->getOrigin()->getName()] = $error->getMessage();
                    }
                }
            } else {
                $data["error"] = true;
                $data["message"] = $this->get("translator")->trans("profile.message.update.error");
            }
            return new JsonResponse($data, $reponseStatus);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("global.error.not_ajax_request"));
            return $this->redirectToRoute('home');
        }
    }
}