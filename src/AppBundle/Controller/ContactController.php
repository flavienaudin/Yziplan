<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 14/09/2016
 * Time: 12:39
 */

namespace AppBundle\Controller;

use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use ATUserBundle\Entity\AccountUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class ContactController extends Controller
{
    /**
     * Get the contact list of the User to populate the BootGrid table
     * Post Request :
     * - current : n => n° "page" affichée
     * - rowCount : n => nb de résultat par "page", -1 : tous les résultats
     * - sort[fieldname] = [asc|desc] => colonne ayant un tri activé
     * - searchPhrase : [''|string] Entrée du formulaire de recherche dans la table
     * @Route("/{_locale}/get_contacts", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="getUserContacts", methods={"POST"})
     */
    public function getUserContactsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        if ($request->isXmlHttpRequest()) {
            $user = $this->getUser();
            if ($user instanceof AccountUser) {
                $contactManager = $this->get("at.manager.contact");
                $data = $contactManager->getFilteredContactsOfUser($user, $request->request->get("searchPhrase", ""), $request->request->get("rowCount", 10),
                    $request->request->get("current", 1), $request->request->get("sort", array()));
                return new JsonResponse($data, Response::HTTP_OK);
            } else {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("global.error.unauthorized_access");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            }
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("global.error.not_ajax_request"));
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/{_locale}/add-contact", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="addContact", methods={"POST"})
     */
    public function addContactAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        if ($request->request->has("email-contacts")) {
            $emailsToAdd = $request->request->get('email-contacts');
            $contactManager = $this->get("at.manager.contact");
            if ($contactManager->addContact($this->getUser(), $emailsToAdd)) {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans("contacts.message.add_contacts.success");
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator.default")->trans("contacts.message.add_contacts.success"));
                    return $this->redirectToRoute('fos_user_profile_show');
                }
            } else {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::WARNING_TYPE][] = $this->get("translator")->trans("contacts.message.add_contacts.error");
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $this->addFlash(FlashBagTypes::WARNING_TYPE, $this->get("translator.default")->trans("contacts.message.add_contacts.error"));
                    return $this->redirectToRoute('fos_user_profile_show');
                }
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator.default")->trans("global.error.invalid_form");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator.default")->trans("global.error.invalid_form"));
                return $this->redirectToRoute('home');
            }
        }
    }


    /**
     * @Route("/{_locale}/delete-contact", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="deleteContact", methods={"POST"})
     */
    public function deleteContactAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        if ($request->request->has("email-contact")) {
            $emailToDelete = $request->request->get('email-contact');
            $contactManager = $this->get("at.manager.contact");
            if ($contactManager->removeContact($this->getUser(), $emailToDelete)) {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans("contacts.message.remove_contact.success");
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator.default")->trans("contacts.message.remove_contact.success"));
                    return $this->redirectToRoute('fos_user_profile_show');
                }
            } else {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::WARNING_TYPE][] = $this->get("translator")->trans("contacts.message.remove_contact.error");
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $this->addFlash(FlashBagTypes::WARNING_TYPE, $this->get("translator.default")->trans("contacts.message.remove_contact.error"));
                    return $this->redirectToRoute('fos_user_profile_show');
                }
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator.default")->trans("global.error.invalid_form");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator.default")->trans("global.error.invalid_form"));
                return $this->redirectToRoute('home');
            }
        }
    }
}