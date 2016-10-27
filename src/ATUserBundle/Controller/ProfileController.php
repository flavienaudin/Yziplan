<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/06/2016
 * Time: 11:23
 */

namespace ATUserBundle\Controller;

use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Entity\User\Contact;
use AppBundle\Form\User\AppUserEmailType;
use AppBundle\Form\User\ContactType;
use AppBundle\Manager\AppUserInformationManager;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use AppBundle\Utils\Response\FileInputJsonResponse;
use ATUserBundle\Entity\AccountUser;
use FOS\UserBundle\Controller\ProfileController as BaseController;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Class ProfileController
 * @package ATUserBundle\Controller
 * @Route("/{_locale}/profile", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
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

        /** FORMs : AppUserInformation */
        $appUserInformationManager = $this->get("at.manager.app_user_information");
        $appUserInformationManager->retrieveAppUserInformation($user);
        $appUserInfoPersonalsForm = $appUserInformationManager->createPersonalInformationForm();
        $appUserContactDetailsForm = $appUserInformationManager->createContactDetailsForm();
        $appUserInfoComplementariesForm = $appUserInformationManager->createComplementaryInformationForm();

        /** FORM : AppUserEmail */
        /** @var FormInterface $appUserEmailForm */
        $appUserEmailForm = $this->createForm(AppUserEmailType::class, new AppUserEmail());

        /** FORM : Contact Tab */
        $addContactForm = $this->createForm(ContactType::class, new Contact());

        return $this->render('FOSUserBundle:Profile:show.html.twig', array(
            'user' => $user,
            'form_app_user_info_personals' => ($appUserInfoPersonalsForm != null ? $appUserInfoPersonalsForm->createView() : null),
            'form_contact_details' => ($appUserContactDetailsForm != null ? $appUserContactDetailsForm->createView() : null),
            'form_app_user_info_complementaries' => ($appUserInfoComplementariesForm != null ? $appUserInfoComplementariesForm->createView() : null),
            'form_appuseremail' => $appUserEmailForm->createView(),
            'form_add_contact' => $addContactForm->createView()
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
     * @Route("/update/personals", name="updateUserPersonalInformation", methods={"POST"})
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
                    $appUserInformationManager->updateAppUserInformation();
                    // TODO : Gérer le format de la date en fonction de la locale
                    $data[AppJsonResponse::DATA] = array(
                        'public-name' => (!empty($appUserInformation->getPublicName()) ? $appUserInformation->getPublicName() : '-'),
                        'legal-status' => ($appUserInformation->getLegalStatus() != null ? $this->get('translator')->trans($appUserInformation->getLegalStatus()) : '-'),
                        'firstname' => (!empty($appUserInformation->getFirstName()) ? $appUserInformation->getFirstName() : '-'),
                        'lastname' => (!empty($appUserInformation->getLastName()) ? $appUserInformation->getLastName() : '-'),
                        'gender' => ($appUserInformation->getGender() != null ? $this->get('translator')->trans($appUserInformation->getGender()) : '-'),
                        'birthday' => ($appUserInformation->getBirthday() != null ? $appUserInformation->getBirthday()->format("d/m/Y") : '-'),
                        'nationality' => (!empty($appUserInformation->getNationality()) ? $appUserInformation->getNationality() : '-')
                    );
                    $personalInformationForm = $appUserInformationManager->createPersonalInformationForm();
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#userPersonals_form_container'] =
                        $this->renderView('@ATUser/Profile/partials/personal_information_form.html.twig', ['form_app_user_info_personals' => $personalInformationForm->createView()]);
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#userPersonals_form_container'] =
                        $this->renderView('@ATUser/Profile/partials/personal_information_form.html.twig', ['form_app_user_info_personals' => $personalInformationForm->createView()]);
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            } else if ($personalInformationForm->isValid()) {
                $appUserInformationManager->updateAppUserInformation();
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
     * @Route("/update/contact-details", name="updateUserContactDetails", methods={"POST"})
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
                    $appUserInformationManager->updateAppUserInformation();
                    $data[AppJsonResponse::DATA] = array(
                        'living-country' => (!empty($appUserInformation->getLivingCountry()) ? Intl::getRegionBundle()->getCountryName($appUserInformation->getLivingCountry()) : '-'),
                        'living-city' => ($appUserInformation->getLivingCity() != null ? $appUserInformation->getLivingCity() : '-')
                    );
                    $contactDetailsForm = $appUserInformationManager->createContactDetailsForm();
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#userContactDetails_form_container'] =
                        $this->renderView('@ATUser/Profile/partials/contact_details_form.html.twig', ['form_contact_details' => $contactDetailsForm->createView()]);
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#userContactDetails_form_container'] =
                        $this->renderView('@ATUser/Profile/partials/contact_details_form.html.twig', ['form_contact_details' => $contactDetailsForm->createView()]);
                    return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            } else if ($contactDetailsForm->isValid()) {
                $appUserInformationManager->updateAppUserInformation();
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
     * @Route("/update/complementaries", name="updateUserComplementatyInformation")
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
            $complementaryInformationForm = $appUserInformationManager->createComplementaryInformationForm();

            $complementaryInformationForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($complementaryInformationForm->isValid()) {
                    $appUserInformationManager->updateAppUserInformation();
                    $data[AppJsonResponse::DATA]["marital-status"] = empty($appUserInformation->getBiography()) ? "-" : $this->get("translator")->trans($appUserInformation->getMaritalStatus());
                    $data[AppJsonResponse::DATA]["biography"] = nl2br(empty($appUserInformation->getBiography()) ?
                        $this->get("translator")->trans("profile.show.profile_information.complementaries.biography.empty") : $appUserInformation->getBiography());
                    $data[AppJsonResponse::DATA]["interets"] = nl2br(empty($appUserInformation->getInterests()) ?
                        $this->get("translator")->trans("profile.show.profile_information.complementaries.interests.empty") : $appUserInformation->getInterests());
                    $data[AppJsonResponse::DATA]["food-conveniences"] = nl2br(empty($appUserInformation->getFoodConveniences()) ?
                        $this->get("translator")->trans("profile.show.profile_information.complementaries.food_conveniences.empty") : $appUserInformation->getFoodConveniences());

                    $complementaryInformationForm = $appUserInformationManager->createComplementaryInformationForm();
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#userComplementaries_form_container'] =
                        $this->renderView('@ATUser/Profile/partials/complementary_information_form.html.twig', ['form_app_user_info_complementaries' => $complementaryInformationForm->createView()]);
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#userComplementaries_form_container'] =
                        $this->renderView('@ATUser/Profile/partials/complementary_information_form.html.twig', ['form_app_user_info_complementaries' => $complementaryInformationForm->createView()]);
                    return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            } else if ($complementaryInformationForm->isValid()) {
                $appUserInformationManager->updateAppUserInformation();
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
     * @Route("/update/avatar", name="updateUserAvatar")
     */
    public function updateUserAvatar(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $imageFile = $request->files->get('avatarImageInput');
            /** @var AppUserInformationManager $appUserInformationManager */
            $appUserInformationManager = $this->get("at.manager.app_user_information");
            $appUserInformation = $appUserInformationManager->retrieveAppUserInformation($this->getUser());
            if ($appUserInformation->getAvatar() != null) {
                $this->get('at.uploader.file')->delete($appUserInformation->getAvatar());
            }
            $appUserInformation->setAvatar($imageFile);
            $appUserInformationManager->updateAppUserInformation();
            $data = array();
            // TODO : Supprimer après phase de TEST
            if ($appUserInformation->getAvatar() != null) {
                $avatarDirURL = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/' . $this->get('at.uploader.file')->getWebRelativeTargetDir();
                $data[FileInputJsonResponse::INITIAL_PREVIEW] = $avatarDirURL . '/' . $appUserInformation->getAvatar();
                $data[FileInputJsonResponse::INITIAL_PREVIEW_CONFIG][] = [
                    FileInputJsonResponse::IPC_URL => $this->generateUrl('deleteUserAvatar', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    FileInputJsonResponse::IPC_KEY => 100,
                    'overwriteInitial' => true
                ];
            }
            return new FileInputJsonResponse($data, Response::HTTP_OK);

        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
            return $this->redirectToRoute('fos_user_profile_show');
        }
    }

    /**
     * @Route("/delete/user-avatar", name="deleteUserAvatar")
     */
    public function deleteUserAvatar(Request $request)
    {
        /** @var AppUserInformationManager $appUserInformationManager */
        $appUserInformationManager = $this->get("at.manager.app_user_information");
        $appUserInformation = $appUserInformationManager->retrieveAppUserInformation($this->getUser());
        if ($appUserInformation->getAvatar() != null) {
            $this->get('at.uploader.file')->delete($appUserInformation->getAvatar());
            $appUserInformation->setAvatar(null);
        }
        $appUserInformationManager->updateAppUserInformation();

        if ($request->isXmlHttpRequest()) {
            $data[FileInputJsonResponse::INITIAL_PREVIEW] = [];
            return new FileInputJsonResponse($data, Response::HTTP_OK);
        } else {
            return $this->redirectToRoute('fos_user_profile_show');
        }
    }
}