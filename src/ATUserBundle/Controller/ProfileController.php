<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/06/2016
 * Time: 11:23
 */

namespace ATUserBundle\Controller;

use AppBundle\Manager\AppUserInformationManager;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\FormUtils;
use ATUserBundle\Entity\AccountUser;
use ATUserBundle\Form\AppUserInfoComplementariesType;
use ATUserBundle\Manager\UserManager;
use FOS\UserBundle\Controller\ProfileController as BaseController;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;


class ProfileController extends BaseController
{
    /**
     * Show the user
     */
    public function showAction(Request $request = null)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);

        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof AccountUser) {
            throw $this->createAccessDeniedException('This user does not have access to this section.');
        }

        /** Profile edition */
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** FORM : User Profile */
        $userForm = $this->get("at.manager.user")->createProfileForm($user);

        /** FORMs : AppUserInformation */
        $appUserInformationManager = $this->get("at.manager.app_user_information");
        $appUserInformationManager->retrieveAppUserInformation($user);
        $appUserInfoPersonalsForm = $appUserInformationManager->createPersonalInformationForm();
        $appUserContactDetailsForm = $appUserInformationManager->createContactDetailsForm();
        $appUserInfoComplementariesForm = $appUserInformationManager->createComplementaryInformationForm();

        return $this->render('FOSUserBundle:Profile:show.html.twig', array(
            'user' => $user,
            'form_connexion_information' => $userForm->createView(),
            'form_app_user_info_personals' => ($appUserInfoPersonalsForm != null ? $appUserInfoPersonalsForm->createView() : $appUserInfoPersonalsForm),
            'form_contact_details' => ($appUserContactDetailsForm != null ? $appUserContactDetailsForm->createView() : $appUserContactDetailsForm),
            'form_app_user_info_complementaries' => ($appUserInfoComplementariesForm != null ? $appUserInfoComplementariesForm->createView() : null)
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
     * @Route("/update-user-profile", name="updateUserConnexionInformation", methods={"POST"})
     */
    public function updateUserConnexionInformationAction(Request $request)
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

                    $data['data']['email'] = $user->getEmail();

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
     * @Route("/update-user-basic-information", name="updateUserPersonalInformation", methods={"POST"})
     */
    public function updateUserPersonalInformationAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $user = $this->getUser();
        $data = array();
        if ($user instanceof AccountUser) {
            $appUserInformationManager = $this->get("at.manager.app_user_information");
            $appUserInformation = $appUserInformationManager->retrieveAppUserInformation($user);
            $personalInformationForm = $appUserInformationManager->createPersonalInformationForm();
            $personalInformationForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($personalInformationForm->isValid()) {
                    $appUserInformationManager->updateAppUserInformation($appUserInformation);
                    // TODO : Gérer le format de la date en fonction de la locale
                    $data["data"] = array(
                        'public-name' => (!empty($appUserInformation->getPublicName()) ? $appUserInformation->getPublicName() : '-'),
                        'legal-status' => ($appUserInformation->getLegalStatus() != null ? $this->get('translator')->trans($appUserInformation->getLegalStatus()) : '-'),
                        'firstname' => (!empty($appUserInformation->getFirstName()) ? $appUserInformation->getFirstName() : '-'),
                        'lastname' => (!empty($appUserInformation->getLastName()) ? $appUserInformation->getLastName() : '-'),
                        'gender' => ($appUserInformation->getGender() != null ? $this->get('translator')->trans($appUserInformation->getGender()) : '-'),
                        'birthday' => ($appUserInformation->getBirthday() != null ? $appUserInformation->getBirthday()->format("d/m/Y") : '-'),
                        'nationality' => (!empty($appUserInformation->getNationality()) ? $appUserInformation->getNationality() : '-')
                    );
                    return new JsonResponse($data, Response::HTTP_OK);
                } else {
                    $data["formErrors"] = array();
                    foreach ($personalInformationForm->getErrors(true) as $error) {
                        $data["formErrors"][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                    }
                    return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            } else if ($personalInformationForm->isValid()) {
                $appUserInformationManager->updateUserAbout($appUserInformation);
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
     * @Route("/update-user-contact-details", name="updateUserContactDetails", methods={"POST"})
     */
    public function updateUserContactDetailsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $user = $this->getUser();
        $data = array();
        if ($user instanceof AccountUser) {
            $appUserInformationManager = $this->get("at.manager.app_user_information");
            $appUserInformation = $appUserInformationManager->retrieveAppUserInformation($user);
            $contactDetailsForm = $appUserInformationManager->createContactDetailsForm();
            $contactDetailsForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($contactDetailsForm->isValid()) {
                    $appUserInformationManager->updateAppUserInformation($appUserInformation);
                    $data["data"] = array(
                        'living-country' => (!empty($appUserInformation->getLivingCountry()) ? Intl::getRegionBundle()->getCountryName($appUserInformation->getLivingCountry()) : '-'),
                        'living-city' => ($appUserInformation->getLivingCity() != null ? $appUserInformation->getLivingCity() : '-')
                    );
                    return new JsonResponse($data, Response::HTTP_OK);
                } else {
                    $data["formErrors"] = array();
                    foreach ($contactDetailsForm->getErrors(true) as $error) {
                        $data["formErrors"][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                    }
                    return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            } else if ($contactDetailsForm->isValid()) {
                $appUserInformationManager->updateUserAbout($appUserInformation);
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
     * @Route("/update-user-biography", name="updateUserComplementatyInformation")
     */
    public function updateUserComplementatyInformationAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $user = $this->getUser();
        $data = array();
        if ($user instanceof AccountUser) {
            /** @var AppUserInformationManager $appUserInformationManager */
            $appUserInformationManager = $this->get("at.manager.app_user_information");
            $appUserInformation = $appUserInformationManager->retrieveAppUserInformation($user);
            $biographyForm = $this->createForm(AppUserInfoComplementariesType::class, $appUserInformation);

            $biographyForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($biographyForm->isValid()) {
                    $appUserInformationManager->updateAppUserInformation($appUserInformation);
                    $data["data"]["marital-status"] = empty($appUserInformation->getBiography()) ? "-"  : $this->get("translator")->trans($appUserInformation->getMaritalStatus());
                    $data["data"]["biography"] = nl2br(empty($appUserInformation->getBiography()) ?
                        $this->get("translator")->trans("profile.show.profile_information.complementaries.biography.empty") : $appUserInformation->getBiography());
                    $data["data"]["interets"] = nl2br(empty($appUserInformation->getInterests()) ?
                        $this->get("translator")->trans("profile.show.profile_information.complementaries.interests.empty") : $appUserInformation->getInterests());
                    $data["data"]["food-conveniences"] = nl2br(empty($appUserInformation->getFoodConveniences()) ?
                        $this->get("translator")->trans("profile.show.profile_information.complementaries.food_conveniences.empty") : $appUserInformation->getFoodConveniences());
                    return new JsonResponse($data, Response::HTTP_OK);
                } else {
                    $data["formErrors"] = array();
                    foreach ($biographyForm->getErrors(true) as $error) {
                        $data["formErrors"][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                    }
                    return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            } else if ($biographyForm->isValid()) {
                $appUserInformationManager->updateAppUserInformation($appUserInformation);
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