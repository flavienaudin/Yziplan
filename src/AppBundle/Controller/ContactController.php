<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 14/09/2016
 * Time: 12:39
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User\Contact;
use AppBundle\Entity\User\ContactEmail;
use AppBundle\Form\User\ContactType;
use AppBundle\Security\ContactVoter;
use AppBundle\Utils\enum\ContactStatus;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use ATUserBundle\Entity\AccountUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Class ContactController
 * @package AppBundle\Controller
 * @Route("/{_locale}/contacts")
 */
class ContactController extends Controller
{
    /**
     * Get the contact list of the User to populate the BootGrid table
     * Post Request :
     * - current : n => n° "page" affichée
     * - rowCount : n => nb de résultat par "page", -1 : tous les résultats
     * - sort[fieldname] = [asc|desc] => colonne ayant un tri activé
     * - searchPhrase : [''|string] Entrée du formulaire de recherche dans la table
     * @Route("/get", name="getUserContacts", methods={"POST"})
     */
    public function getUserContactsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        if ($request->isXmlHttpRequest()) {
            $user = $this->getUser();
            if ($user instanceof AccountUser) {
                $contactManager = $this->get("at.manager.contact");
                $data = $contactManager->getFilteredContactsOfUserAsArray($user, $request->request->get("searchPhrase", ""), $request->request->get("rowCount", 10),
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
     * Get the contacts list of the User to invite
     * Post Request : query (string)
     * @Route("/search/", name="searchContacts", methods={"POST"})
     */
    public function searchContactsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        if ($request->isXmlHttpRequest()) {
            $user = $this->getUser();
            if ($user instanceof AccountUser) {
                $contactManager = $this->get('at.manager.contact');
                $search = $request->get('search');
                $contacts = $contactManager->searchContactsOfUser($user, $search);
                $data = array();
                /** @var Contact $contact */
                foreach($contacts as $contact){
                    $contactAsArray = array();
                    $contactAsArray['text'] = $contact->getDisplayableName();
                    $contactAsArray['value'] = $contact->getEmailToContact();
                    $data[] = $contactAsArray;
                }
                return new AppJsonResponse($data, Response::HTTP_OK);
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
     * @Route("/add", name="addContact")
     */
    public function addContactAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $user = $this->getUser();
        if ($user instanceof AccountUser) {
            $responseStatus = Response::HTTP_OK;
            $contactForm = $this->createForm(ContactType::class, new Contact());
            $contactForm->handleRequest($request);
            if ($contactForm->isSubmitted()) {
                if ($contactForm->isValid()) {
                    $contactManager = $this->get("at.manager.contact");
                    $contact = $contactManager->addContact($contactForm->getData(), $user);
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans('contacts.message.add_contact.success');

                    // Initialize new contactForm
                    $contactForm = $this->createForm(ContactType::class, new Contact());
                    $responseStatus = Response::HTTP_OK;
                } else {
                    $responseStatus = Response::HTTP_BAD_REQUEST;
                }
            }
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#addContact_formcontainer'] =
                    $this->renderView("@App/Contact/partials/contact_form.html.twig", ['modalIdPrefix' => 'addContact', 'contact' => null, 'form_contact' => $contactForm->createView()]);
                return new AppJsonResponse($data, $responseStatus);
            } else {
                // Affichage du formulaire avec ou sans erreur dans une page spécifique
                // TODO : Not Ajax request not supported yet
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
                return $this->redirect($this->generateUrl("fos_user_profile_show") . '#profile-contacts');
            }
        }
        if ($request->isXmlHttpRequest()) {
            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("global.error.unauthorized_access");
            return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("global.error.unauthorized_access"));
            return $this->redirect($this->generateUrl("fos_user_profile_show") . '#profile-contacts');
        }
    }


    /**
     * @Route("/edit/{id}", name="editContact",  defaults={"id" = null})
     * @ParamConverter("contact", class="AppBundle:User\Contact", options={"strip_null" : true})
     */
    public function editContactAction(Contact $contact = null, $id = null, Request $request)
    {
        $contactManager = $this->get("at.manager.contact");
        if ($contact == null && $request->request->has('contact-id')) {
            $contact = $contactManager->retrieveContact($request->request->get('contact-id'));
        }
        $this->denyAccessUnlessGranted(ContactVoter::EDIT, $contact);
        $contactForm = $this->createForm(ContactType::class, $contact);
        $contactForm->handleRequest($request);
        $responseStatus = Response::HTTP_OK;
        if ($contactForm->isSubmitted()) {
            if ($contactForm->isValid()) {
                $contact = $contactManager->updateContact($contactForm->getData(), $this->getUser());
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans('contacts.message.edit_contact.success');
                $responseStatus = Response::HTTP_OK;
            } else {
                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#editContact_formcontainer'] =
                    $this->renderView("@App/Contact/partials/contact_form.html.twig", [
                        'modalIdPrefix' => 'editContact', 'contact' => $contact, 'form_contact' => $contactForm->createView()
                    ]);
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            }
        }
        if ($request->isXmlHttpRequest()) {
            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_APPEND_TO]['#editContact_modalContainer'] =
                $this->renderView("@App/Contact/partials/modal_contact_form.html.twig", [
                    'modalIdPrefix' => 'editContact', 'contact' => $contact, 'form_contact' => $contactForm->createView()
                ]);
            return new AppJsonResponse($data, $responseStatus);
        } else {
            // Affichage du formulaire avec ou sans erreur dans une page spécifique
            // TODO : Not Ajax request not supported yet
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
            return $this->redirect($this->generateUrl("fos_user_profile_show") . '#profile-contacts');
        }
    }

    /**
     * @Route("/delete", name="deleteContact", methods={"POST"})
     */
    public function deleteContactAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        if ($request->request->has("contact-id")) {
            $contactManager = $this->get("at.manager.contact");
            $this->denyAccessUnlessGranted(ContactVoter::DELETE, $contactManager->retrieveContact($request->request->get('contact-id')));
            if ($contactManager->removeContact()) {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans("contacts.message.remove_contact.success");
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator")->trans("contacts.message.remove_contact.success"));
                    return $this->redirectToRoute('fos_user_profile_show');
                }
            } else {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("contacts.message.remove_contact.error");
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                } else {
                    $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("contacts.message.remove_contact.error"));
                    return $this->redirectToRoute('fos_user_profile_show');
                }
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("global.error.invalid_form");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("global.error.invalid_form"));
                return $this->redirectToRoute('home');
            }
        }
    }
}