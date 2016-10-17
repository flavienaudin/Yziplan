<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 12/10/2016
 * Time: 14:49
 */

namespace AppBundle\Controller;


use AppBundle\AppEvents;
use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Form\User\AppUserEmailType;
use AppBundle\Manager\ApplicationUserManager;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\FormUtils;
use AppBundle\Utils\Response\AppJsonResponse;
use ATUserBundle\Entity\AccountUser;
use FOS\UserBundle\Event\FormEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Test\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Class AppUserEmailController
 * @package ATUserBundle\Controller
 * @Route("{_locale}/app-user-email", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class AppUserEmailController extends Controller
{

    /**
     * @Route("/addAppUserEmail", name="addAppUserEmail", methods={"POST"})
     */
    public function addAppUserEmail(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $user = $this->getUser();
        $data = array();
        if ($user instanceof AccountUser) {
            $applicationUserManager = $this->get('at.manager.application_user');
            $applicationUserManager->setApplicationUser($user->getApplicationUser());
            $eventDispatcher = $this->get("event_dispatcher");
            $newAppUserEmail = new AppUserEmail();
            $appUserEmailForm = $this->createForm(AppUserEmailType::class, $newAppUserEmail);
            $appUserEmailForm->handleRequest($request);
            if ($appUserEmailForm->isValid() && (($appUserEmail = $applicationUserManager->treatAddAppUserEmailForm($appUserEmailForm)) != null)) {
                $formEvent = new FormEvent($appUserEmailForm, $request);
                $eventDispatcher->dispatch(AppEvents::APPUSEREMAIL_ADD_SUCCESS, $formEvent);
                $entityManager = $this->get('doctrine.orm.entity_manager');
                $entityManager->persist($appUserEmail);
                $entityManager->flush();

                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_APPEND_TO]["#ul-profile-appuseremails"] =
                        $this->renderView("@App/AppUserEmail/partials/display_appuseremail.html.twig", ['appuseremail' => $appUserEmail]);
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] =
                        $this->get("translator")->trans("appuseremail.associate.message.check_mailbox", ["%email%" => $appUserEmail->getEmail()]);
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator")->trans("appuseremail.associate.message.check_mailbox", ["%email%" => $appUserEmail->getEmail()]));
                    return $this->redirectToRoute("fos_user_profile_show");
                }
            } else {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::FORM_ERRORS] = array();
                    foreach ($appUserEmailForm->getErrors(true) as $error) {
                        $data[AppJsonResponse::FORM_ERRORS][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                    }
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                } else {
                    $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("profile.message.update.error"));
                    return $this->redirectToRoute("fos_user_profile_show");
                }
            }
        }
        if ($request->isXmlHttpRequest()) {
            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("profile.message.update.error");
            return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("profile.message.update.error"));
            return $this->redirectToRoute("fos_user_profile_show");
        }
    }


    /**
     * Receive the confirmation token of AppUserEmail to validate the association.
     *
     * @param Request $request
     * @param string $token The confirmationTolen of the AppUserEmail
     * @return Response
     * @Route("/confirm/{token}", name="confirmAppUserEmailAssociation")
     */
    public function confirmAppUserEmailAssociationAction(Request $request, $token)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        /** @var ApplicationUserManager $applicationUserManager */
        $applicationUserManager = $this->get('at.manager.application_user');
        $appUserEmail = $applicationUserManager->findAppUserEmailByConfirmationToken($token);

        if (null === $appUserEmail) {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("appuseremail.associate.message.not_found", ['%token%' => $token]));
            return $this->redirectToRoute("fos_user_profile_show");
        }
        if ($this->getUser() != $appUserEmail->getApplicationUser()->getAccountUser()) {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("appuseremail.associate.message.wrong_user"));
            return $this->redirectToRoute('fos_user_profile_show');
        }

        $appUserEmail->setConfirmationToken(null);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $entityManager->persist($appUserEmail);
        $entityManager->flush();

        $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get('translator')->trans("appuseremail.associate.message.association_validated", ['%email%' => $appUserEmail->getEmail()]));
        return $this->redirectToRoute('fos_user_profile_show');
    }

    /**
     * @Route("/edit/{id}", name="editAppUserEmail")
     * @ParamConverter("appUserEmail", class="AppBundle:User\AppUserEmail")
     */
    public function editAppUserEmailAction(Request $request, AppUserEmail $appUserEmail)
    {
        if ($request->isXmlHttpRequest()) {
            $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
            $user = $this->getUser();
            if ($user instanceof AccountUser) {
                if ($appUserEmail == null || $appUserEmail->getApplicationUser()->getAccountUser() != $user) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("appuseremail.edit.message.error");
                    return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
                }
                /** @var FormInterface $form */
                $form = $this->get('form.factory')->createNamed('edit_app_user_email', AppUserEmailType::class, $appUserEmail);
                $form->handleRequest($request);
                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
                        /** @var AppUserEmail $appUserEmail */
                        $appUserEmail = $form->getData();
                        $entityManager = $this->get('doctrine.orm.entity_manager');
                        $entityManager->persist($appUserEmail);
                        $entityManager->flush();
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]["#appuseremail_" . $appUserEmail->getId()] =
                            $this->renderView("@App/AppUserEmail/partials/display_appuseremail.html.twig", ['appuseremail' => $appUserEmail]);
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::FORM_ERRORS] = array();
                        foreach ($form->getErrors(true) as $error) {
                            $data[AppJsonResponse::FORM_ERRORS][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                        }
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                }
                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_APPEND_TO]["#editAppUserEmail_modalContainer"] =
                    $this->renderView("@App/AppUserEmail/partials/modal_appuseremail_form.html.twig", [
                        'modalIdPrefix' => "editAppUSerEmail",
                        'appuseremail' => $appUserEmail,
                        'form_appuseremail' => $form->createView()
                    ]);
                return new AppJsonResponse($data, Response::HTTP_OK);
            } else {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("appuseremail.edit.message.error");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            }
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("global.error.not_ajax_request"));
            return $this->redirectToRoute('fos_user_profile_show');
        }
    }

    /**
     * @Route("/delete/{id}", name="deleteAppUserEmail")
     * @ParamConverter("appUserEmail", class="AppBundle:User\AppUserEmail")
     */
    public function deleteAppUserEmailAction(Request $request, AppUserEmail $appUserEmail)
    {
        if ($request->isXmlHttpRequest()) {
            $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
            $user = $this->getUser();
            if ($user instanceof AccountUser) {
                if ($appUserEmail == null || $appUserEmail->getApplicationUser()->getAccountUser() != $user) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("appuseremail.delete.message.error.default");
                    return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
                }
                if ($appUserEmail->getApplicationUser()->getAccountUser()->getEmailCanonical() != $appUserEmail->getEmailCanonical()) {
                    $entityManager = $this->get('doctrine.orm.entity_manager');
                    $entityManager->remove($appUserEmail);
                    $entityManager->flush();
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("appuseremail.delete.message.error.main_email");
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            } else {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("appuseremail.delete.message.error.default");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            }
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("global.error.not_ajax_request"));
            return $this->redirectToRoute('fos_user_profile_show');
        }
    }

}